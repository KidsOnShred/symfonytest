<?php

namespace Tom\Bundle\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tom\Bundle\TestBundle\Entity\User;

class DefaultController extends Controller
{
    protected $session;

    protected $em;

    public function indexAction(Request $request)
    {
    	//Get entity manager
        $this->em = $this->getDoctrine()->getManager();

        //Get session
        $this->session = $this->getRequest()->getSession();

        //Create Form
        $form = $this->createFormBuilder()
    		->add('email', 'email')
    		->add('password', 'password')
    		->add('submit', 'submit')
    		->getForm()
    	;

        //Handle the submit request
    	$form->handleRequest($request);

        //Error message for incorrect password
        $passwordError = '';

        //If valid process user
    	if ($form->isValid())
    	{
            //Check database for user if not found create one
            if($this->addUserAction($form))
    	        return $this->redirect($this->generateUrl('tom_test_myaccount'));
            else
                $passwordError = 'Password Incorrect';  
        }

        //Render page
        return $this->render('TomTestBundle:Default:index.html.twig', array(
        	'form' => $form->createView(),
            'passwordError' => $passwordError,
        ));
    }
    public function addUserAction($form)
    {
        //Extract form data;
        $data = $form->getData();
        extract($data);    

        //Get user from database
        $User = $this->em->getRepository('TomTestBundle:User')
            ->findOneByEmail($email);
        
        //Check if user exists
        if (!$User)
        {
            //If new user, create and add to database and report success
            $User = new User($data);
            $em = $this->getDoctrine()->getManager();
            $em->persist($User);
            $em->flush();

            //Update session
            $this->setSessionAction($User);

            return true;
        }
        else
        {
            //If user exists check their password and log them in
            if ($User->checkPassword($password))
            {
                $this->setSessionAction($User);
                return $this->redirect($this->generateUrl('tom_test_myaccount'));
            }
        }
        //password is incorrect return false
        return false;
    }
    public function setSessionAction($User)
    {
        //Store session to keep user logged in
        $this->session->set('userId', $User->getId());
    }
    public function getUserBySessionAction()
    {
        //Get entity manager
        $this->em = $this->getDoctrine()->getManager();
        return $this->em->getRepository('TomTestBundle:User')->findOneById($this->session->get('userId'));
    }
    public function myAccountAction()
    {
    	//Get entity manager
        $this->em = $this->getDoctrine()->getManager();
        
        //Get session
        $this->session = $this->getRequest()->getSession();

        /* Check if access to myaccount is authorised */

        //Get User
        $User = $this->getUserBySessionAction();

        //If User doesn't exist redirect to login
        if(!$User)
            return $this->redirect($this->generateUrl('tom_test_homepage'));

        //Route logout button
        $urlLogout = $this->generateUrl('tom_test_homepage');

        //Render
        return $this->render('TomTestBundle:Default:myaccount.html.twig', array('email' => $User->getEmail(), 'logout' => $urlLogout));
    }
    public function logoutAction()
    {
        return $this->redirect($this->generateUrl('tom_test_homepage'));
    }
}
