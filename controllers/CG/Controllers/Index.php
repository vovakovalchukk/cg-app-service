<?php
namespace CG\Controllers;

use Slim\Slim;
use Slim\View;
use Zend\Db\Adapter\Adapter;
use Zend\Di\Di;

class Index
{
    protected $slim;
    protected $view;
    protected $di;

    public function __construct(Slim $app, View $view, Di $di)
    {
        $this->setSlim($app)
            ->setView($view)
            ->setDi($di);
    }

    public function index()
    {
        $view = $this->getView();
        $view->set('framework', 'Slim');

        $di = $this->getDi();
        try {
            $dbAdapter = $di->get('readDb');
            $view->set('db', $dbAdapter->getCurrentSchema());
            $view->set('tables', $dbAdapter->query('SHOW TABLES', Adapter::QUERY_MODE_EXECUTE));
        }
        catch (\Exception $exception) {
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

    public function getDi()
    {
        return $this->di;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }
}