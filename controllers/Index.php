<?php
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\Exception\ExceptionInterface;
use Slim\Slim;
use Slim\View;
use Zend\Db\Adapter\Adapter;

class Index implements ServiceLocatorAwareInterface
{
    protected $slim;
    protected $view;
    protected $serviceLocator;

    public function __construct(Slim $app, View $view, ServiceLocatorInterface $serviceLocator = null)
    {
        $this->setSlim($app)
             ->setView($view);

        if ($serviceLocator) {
            $this->setServiceLocator($serviceLocator);
        }
    }

    public function index()
    {
        $view = $this->getView();
        $view->set('framework', 'Slim');

        $serviceLocator = $this->getServiceLocator();
        try {
            $dbAdapter = $serviceLocator->get('readDb');
            $view->set('db', $dbAdapter->getCurrentSchema());
            $view->set('tables', $dbAdapter->query('SHOW TABLES', Adapter::QUERY_MODE_EXECUTE));
        }
        catch (ExceptionInterface $exception) {
            // If no Db Adapter - Application created without database
        }

        $view->display('index.phtml');
    }

    public function setSlim(Slim $slim)
    {
        $this->slim = $slim;
        return $this;
    }

    public function getSlim()
    {
        return $this->slim;
    }

    public function setView(View $view)
    {
        $this->view = $view;
        return $this;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}