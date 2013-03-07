<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {

    }

    public function preDispatch()
    {

    }

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) 
        {
        } else {
            $this->_redirect('/dive');
        }
    }
}
