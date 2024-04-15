<?php

require_once 'vendor/autoload.php'; 

session_start();

class UserAuth{

    public string $errors;

    public function __construct() {
        $this->errors = false;
    }
    
    /**
     * register user
     *
     * @param  string $name
     * @param  string $password
     * @param  string $email
     * @return boolean
     */
    public function register(string $name, string $password, string $email) {

            $existingUser = $this->getUserByEmail($email);
        
            if ($existingUser !== null)
            {
                $this->errors .= "User with that email already exists!\n";
            }

            if(strlen($name) > 50 || strlen($name) < 4)
            {
                $this->errors .= "Name should be between 4 and 50 characters!\n";
            }
    
            if(empty($name) || empty($email) || empty($password))
            {
                $this->errors .= "Please fill out all the fields!";
            }
    
            if(strlen($password) > 50 || strlen($password) < 8)
            {
                $this->errors .= "Password should be between 8 and 50 characters! \n";
            }
    
            if(!empty($this->errors))
            {
                return false;
            }
    
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userModel = new Model\UserModel();

            if($userModel->addUser($name, $hashedPassword, $email)){
                return true;
            }else{
                $this->errors .= "Something went wrong! \n";
                return false;
            }
          
    }
    
    /**
     * login user
     *
     * @param  string $email
     * @param  string $password
     * @return void
     */
    public function login(string $email, string $password) {

        try {
            if(empty($email) || empty($password))
            {
                $this->errors .= 'Please fill out all the fields!';
            }
    
            $userData = $this->getUserByEmail($email);
    
            if (!$userData) {
                $this->errors .= "User not found!";
            }
    
            if(!empty($this->errors))
            {
                return false;
            }
    
            // Verify password
            if (password_verify($password, $userData['password'])) {
                $_SESSION['user_id'] = $userData['id'];
                return true;
            } else {
                $this->errors .= "Incorrect password!";
                return false;
            }
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        } 
    }
    
    /**
     * Get a user by their email
     *
     * @param  string $email
     * @return void
     */
    private function getUserByEmail(string $email) {

        $userModel = new Model\UserModel();
        return $userModel->retrieveUserByEmail($email);
    }
    
    public function getErrors()
    {
        return nl2br($this->errors);
    }

    public function getLoggedUser()
    {
        try {
            
            if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id']))
            {
                $userModel = new Model\UserModel();
                return $userModel->loggedUser();
            }else{
                return false;
            }
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        } 
    }
}



