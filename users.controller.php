<?php

class UsersController extends Controller{

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->model = new User();
    }

    public function login(){
        if($_POST && isset($_POST['login']) && isset($_POST['password'])){
            $user = $this->model->getByLogin($_POST['login']);
            $hash = md5(Config::get('salt') . $_POST['password']);
            if ($user && $user['is_active'] && $hash == $user['password']){
                Session::set('login', $user['login']);
                Session::set('role', $user['role']);
                Session::set('user_id', $user['id']);
            } else {
                Session::setFlash('Wrong login: ' . $_POST['login'] . ' or password.');
            }
            if ($user['role'] == 'admin') {
                Router::redirect('/admin/');
            } elseif($user['role'] == 'user') {
                Router::redirect('/');
            }
        }
    }

    public function registration(){
        if ($_POST && isset($_POST['login']) && isset($_POST['password']) && isset($_POST['email']) && $_POST['password'] && $_POST['password_confirmation']){
        	if($_POST['password'] == $_POST['password_confirmation']){
			$login = trim($_POST['login']);
			$email = trim($_POST['email']);
			$password = md5(Config::get('salt') . trim($_POST['password']));
			$new_user = $this->model->register($login, $email, $password);
			if ($new_user){
		                Session::setFlash('User with login: ' . $login . ' or email: ' . $email .  ' is already exist.');
		            } else {
		                Session::setFlash('User with login: ' . $login . ' with email' . $email .  ' was created.');
		        }
        	}else{
        		Session::setFlash('Passwords is not identical');
        	}
        } else{
		Session::setFlash('Please, fill in all the fields!');
	}
    }

    public function recovery(){
        if ($_POST && $_POST['login']){
            $login = trim($_POST['login']);
            $temp_password = md5(date("H:i:s"));
            $recovery = $this->model->recovery($login, $temp_password);
            $user = $this->model->getByLogin($_POST['login']);

            if (!$recovery) {
                Session::setFlash('There are no any user with login: ' . $login);
            } else {
                mail($user['email'], ' Password recovery', 'For login use password: ' . $temp_password . ' . You can change this password after login.');
                Session::setFlash('Letter with your new password was sent on you email');
            }
        }
    }

    /*Dont working*/

    public function change_password(){
        if ($_POST && $_POST['old_password'] && $_POST['new_password']){
            $login = Session::get('login');
            $new_password = trim($_POST['new_password']);
            $old_password = trim($_POST['old_password']);
            $changePassword = $this->model->setNewPassword($login, $old_password, $new_password);

            if (!$changePassword) {
                Session::setFlash('You have typed wrong password.');
            } else {
                Session::setFlash('You have changed your password succesfully!');
            }
        }
    }

    public function logout(){
        Session::destroy();
        Router::redirect('/');
    }
}
