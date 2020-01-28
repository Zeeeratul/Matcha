<?php

class Controller
{
    protected $f3;
    protected $db;

    function beforeroute() {
        $route = $this->f3->get('PARAMS.0');
        $User = new User($this->db);
        $Profile = new Profile($this->db);
        $id = $this->f3->get('SESSION.uid');
        if ($User->checkSession($id, $this->f3->get('SESSION.user')) == FALSE)
        {
            if (!($route == '/login' || $route == '/create' || $route == '/forgotten' || $route == '/resetPassword' || $route == '/reset/' . $this->f3->get('PARAMS.token') || $route == '/validate/' . $this->f3->get('PARAMS.token')))
            {
                $this->f3->reroute('/login');
                exit;
            }
        }
        else
        {
            if (!$Profile->validatedProfile($id) && $route != '/buildProfile' && $route != '/logout')
                $this->f3->reroute('/buildProfile');
            $User = new User($this->db);
            $User->updateOnlineTable($this->f3->get('SESSION.uid'));
        }

    }
    
    function afterroute () {
        echo Template::instance()->render('layout.html');
    }

    function __construct()
    {
        $f3 = Base::instance();

        $db=new DB\SQL(
            $f3->get('db_dns') . $f3->get('db_name'),
            $f3->get('db_user'),
            $f3->get('db_pass')
        );

        $this->f3 = $f3;
        $this->db = $db;
    }

}