<?php
namespace CG\Ekm\Registration\Action;

use CG\Ekm\Account\CreationService as EkmAccountCreationService;
use CG\Ekm\Account\Service as EkmAccountService;
use CG\Ekm\Registration\Entity as Registration;
use CG\Ekm\Registration\Service as RegistrationService;
use CG\Http\StatusCode;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\InvalidInputException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\Entity as User;
use CG\User\Service as UserService;
use CG_Register\Registration as ProcessedRegistration;
use CG_Register\Service\RegisterService;
use Exception;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;

class Register implements LoggerAwareInterface
{
    use LogTrait;

    const MAX_ATTEMPTS_UPDATE_REGISTRATION_ON_SUCCESS = 3;
    const MAX_ATTEMPTS_CREATE_ROOT_OU_AND_USER = 3;
    const MAX_ATTEMPTS_CONNECT_EKM_ACCOUNT = 3;

    const LOG_CODE = 'RegistrationRegisterAction';
    const LOG_CODE_REGISTRATION_ATTEMPT = 'RegistrationAttempt';
    const LOG_MSG_REGISTRATION_ATTEMPT = 'Attempting to register, email = %s';
    const LOG_CODE_REGISTRATION_INVALID = 'RegistrationInvalid';
    const LOG_MSG_REGISTRATION_INVALID = 'Invalid form input, email = %s';
    const LOG_MSG_REGISTRATION_CONFLICT = 'User tried to register with email address = %s but it already existed';
    const LOG_CODE_UNPROCESSABLE_ENTITY = 'UnprocessableEntity';
    const LOG_MSG_REGISTRATION_UNPROCESSABLE_ENTITY = 'Unprocessable entity when registering, email = %s';
    const LOG_CODE_REGISTRATION_CANT_CONNECT_TO_USER_DIRECTORY = 'RegistrationCantConnectToUserDirectory';
    const LOG_MSG_REGISTRATION_CANT_CONNECT_TO_USER_DIRECTORY = 'Couldn\'t connect to User Directory when logging in, email: %s';
    const LOG_CODE_PASSWORD_INCORRECT = 'PasswordIncorrect';
    const LOG_MSG_REGISTRATION_PASSWORD_INCORRECT = 'Unknown error when registering. Email address: %s';
    const LOG_CODE_CONNECT_EKM_ACCOUNT_FAILED = 'ConnectEkmAccountFailed';
    const LOG_MSG_CONNECT_EKM_ACCOUNT_FAILED = 'Failed to connect EKM account. EKM Username: %s, Email address: %s, Ou: %d, User: %d';
    const LOG_CODE_CREATE_ROOT_OU_AND_CREATE_USER_FAILED = 'ConnectEkmAccountFailed';
    const LOG_MSG_CREATE_ROOT_OU_AND_CREATE_USER_FAILED = 'Failed to create root Ou and user. EKM Username: %s, Email address: %s';
    const LOG_CODE_NOT_FOUND = 'NotFound';
    const LOG_MSG_REGISTRATION_NOT_FOUND = 'Failed to find registration. EKM Username: %s';
    const LOG_CODE_CONFLICT = 'Conflict';
    const LOG_MSG_CONFLICT_UPDATE_REGISTRATION_ON_SUCCESS = 'Failed to update registration, a conflict occurred and max attempts (%d) were exceeded. Email address: %s, Registration: %d';

    /** @var  RegistrationService $registrationService */
    protected $registrationService;
    /** @var  EkmAccountService $ekmAccountService */
    protected $ekmAccountService;
    /** @var  EkmAccountCreationService $ekmAccountCreationService */
    protected $ekmAccountCreationService;
    /** @var  RegisterService $registerService */
    protected $registerService;
    /** @var  UserService $userService */
    protected $userService;
    /** @var  OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        RegistrationService $registrationService,
        EkmAccountService $ekmAccountService,
        EkmAccountCreationService $ekmAccountCreationService,
        RegisterService $registerService,
        UserService $userService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->registrationService = $registrationService;
        $this->ekmAccountService = $ekmAccountService;
        $this->ekmAccountCreationService = $ekmAccountCreationService;
        $this->registerService = $registerService;
        $this->userService = $userService;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function __invoke(string $ekmUsername, string $token): void
    {
        try {
            $registration = $this->registrationService->fetchByEkmUsernameAndToken($ekmUsername, $token);
        } catch(NotFound $e) {
            $this->logErrorException($e, static::LOG_MSG_REGISTRATION_NOT_FOUND, ['ekmUsername' => $ekmUsername], [static::LOG_CODE, static::LOG_CODE_NOT_FOUND]);
            throw $e;
        }

        try {
            /**
             * CGIV-8903: Rather than automatically updating EKM accounts post registration which could disrupt service on existing accounts,
             * we update the registration with the root ou id of the existing account and mark the registration as completed
             */
            /** @var Account $account */
            $account = $this->ekmAccountService->fetchByEkmUsername($registration->getEkmUsername(), $registration->getEmailAddress());
            /** @var int $rootOrganisationUnitId */
            $rootOrganisationUnitId = $this->organisationUnitService->getRootOuFromOuId($account->getOrganisationUnitId())->getId();
        } catch(NotFound $e) {
            /** @var int $rootOrganisationUnitId */
            $rootOrganisationUnitId = $this->processRegistration($registration);
        }

        $this->updateRegistrationOnCompletion($rootOrganisationUnitId, $registration);
        return;
    }

    public function fetchEkmAccountByEkmUsername(string $ekmUsername)
    {
        $filter = new AccountFilter('all', 1);
        $filter->setChannel('ekm')
            ->setEkmUsername($ekmUsername);
        $accounts = $this->accountClientService->fetchByFilter($filter);
        return $accounts->getFirst();
    }

    protected function processRegistration(Registration $registration): int
    {
        /** @var string $ekmUsername */
        $ekmUsername = $registration->getEkmUsername();
        /** @var string $email */
        $email = $registration->getEmailAddress();

        try {
            $processedRegistration = $this->createRootOuAndUser($this->getMapper()->toRegistrationData($registration));
        } catch(Exception $e) {
            $this->logErrorException($e, static::LOG_MSG_CREATE_ROOT_OU_AND_CREATE_USER_FAILED, ['ekmUsername' => $ekmUsername, 'email' => $email], [static::LOG_CODE, static::LOG_CODE_CREATE_ROOT_OU_AND_CREATE_USER_FAILED]);
            throw $e;
        }

        /** @var OrganisationUnit $rootOu */
        $rootOu = $processedRegistration->getRootOrganisationUnit()->getId();
        /** @var User $user */
        $user = $processedRegistration->getUser()->getId();

        try {
            $this->connectEkmAccount($rootOu->getId(), $registration);
        } catch(Exception $e) {
            $this->logErrorException($e, static::LOG_MSG_CONNECT_EKM_ACCOUNT_FAILED, ['ekmUsername' => $ekmUsername, 'email' => $email, 'ou' => $rootOu->getId(), 'user' => $user->getId()], [static::LOG_CODE, static::LOG_CODE_CONNECT_EKM_ACCOUNT_FAILED]);
            throw $e;
        }
        return $rootOu->getId();
    }

    protected function createRootOuAndUser(array $registrationData): ProcessedRegistration
    {
        /** @var int $attempts */
        $attempts = static::MAX_ATTEMPTS_CREATE_ROOT_OU_AND_USER;
        /** @var string $email */
        $email = $registrationData['email'];
        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $this->logInfo(static::LOG_MSG_REGISTRATION_ATTEMPT, [$email], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_ATTEMPT]);
            } catch (InvalidInputException $e) {
                $this->logInfo(static::LOG_MSG_REGISTRATION_INVALID, [$email], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_INVALID]);
                throw $e;
            } catch (ClientErrorResponseException $e) {
                if ($e->getResponse()->getStatusCode() == StatusCode::CONFLICT) {
                    $this->logError(static::LOG_MSG_REGISTRATION_CONFLICT, [$email], [static::LOG_CODE, static::LOG_CODE_CONFLICT]);
                    if ($i == $attempts) {
                        throw $e;
                    }
                    continue;
                } else if ($e->getResponse()->getStatusCode() == StatusCode::UNPROCESSABLE_ENTITY) {
                    $this->logInfo(static::LOG_MSG_REGISTRATION_UNPROCESSABLE_ENTITY, [$email], [static::LOG_CODE, static::LOG_CODE_UNPROCESSABLE_ENTITY]);
                    throw $e;
                }
                $this->logException($e, 'log:critical', __NAMESPACE__);
                throw $e;
            } catch (CurlException $e) {
                $this->logEmergencyException($e, static::LOG_MSG_REGISTRATION_CANT_CONNECT_TO_USER_DIRECTORY, [$email], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_CANT_CONNECT_TO_USER_DIRECTORY]);
                if ($i == $attempts) {
                    throw $e;
                }
                continue;
            } catch (Conflict $e) {
                if ($i == $attempts) {
                    throw $e;
                }
                continue;
            } catch (Exception $e) {
                $this->logAlertException($e, static::LOG_MSG_REGISTRATION_PASSWORD_INCORRECT, [$email], [static::LOG_CODE, static::LOG_CODE_PASSWORD_INCORRECT]);
                throw $e;
            }
        }
        return $this->registerService->register($registrationData);
    }

    protected function connectEkmAccount(int $rootOrganisationUnitId, array $registrationJson): void
    {
        /** @var int $attempts */
        $attempts = static::MAX_ATTEMPTS_CONNECT_EKM_ACCOUNT;
        $accountId = null;
        $accountConnectionParams = [
            'username' => $registrationJson['ekmUsername'],
            'password' => $registrationJson['ekmPassword'],
            'apiKey' => $registrationJson['ekmApiKey'],
            'apiUrl' => $registrationJson['ekmApiEndpoint'],
        ];
        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $this->ekmAccountCreationService->connectAccount(
                    $rootOrganisationUnitId,
                    $accountId,
                    $accountConnectionParams
                );
            } catch (Exception $e) {
                continue;
            }
        }
        return;
    }

    protected function updateRegistrationOnCompletion(int $rootOrganisationUnitId, Registration $registration): void
    {
        /** @var int $attempts */
        $attempts = static::MAX_ATTEMPTS_UPDATE_REGISTRATION_ON_SUCCESS;
        for ($i = 1; $i <= $attempts; $i++) {
            $registration->setRootOuId($rootOrganisationUnitId);
            $registration->setCompletedDate();
            try {
                $this->registrationService->save($registration);
            } catch(Conflict $e) {
                if ($i == $attempts) {
                    $this->logErrorException($e, static::LOG_MSG_CONFLICT_UPDATE_REGISTRATION_ON_SUCCESS, ['attempts' => $attempts, 'email' => $registration->getEmailAddress(), 'registration' => $registration->getId()], [static::LOG_CODE, static::LOG_CODE_CONFLICT]);
                    throw $e;
                }
                continue;
            }
        }
        return;
    }
}