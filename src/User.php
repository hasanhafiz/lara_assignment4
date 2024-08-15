<?php 
namespace App;

use App\Helpers\Utils;

class User {
    private $_data = null,
            $_session_name = 'user',
            $_is_logged_in = false,
            $_filename = 'users',
            $_filepath = '',
            $_is_admin = false;
    
    /**
     * User could be an user id or email
     *
     */
    public function __construct()
    {
        $this->_filepath = "database/{$this->_filename}.json";
        if ( Session::exists( $this->_session_name ) ) {            
            $this->get( Session::get($this->_session_name) );
            $this->_is_logged_in = true;  
            if ( $this->data()->is_admin ) {
                $this->_is_admin = true;
                echo 'User is admin';
            }  
        }
    }
    
    public function load()
    {
        if ( file_exists( $this->_filepath ) ) {
            return json_decode( file_get_contents( $this->_filepath ), true );
        } else {
            return [];
        }
    }
        
    public function create($fields = []): void
    {
        $stored_data = $this->load();
        array_push( $stored_data, $fields );
        file_put_contents( $this->_filepath, json_encode( $stored_data, JSON_PRETTY_PRINT ) );
        
        $this->_data = $this->get( $fields['email'] );
    }
    
    public function data() {
        return $this->_data;
    }
    
    // $user_found = DB::getInstance()->query("SELECT * FROM users WHERE email = ? AND password = ?", [Input::get('email'), Input::get('password')]);        
    // Class Object ( [id] => 5 [username] => saif [password] => 9bc6f3df948901299b56d4b1c0d996f3907f2571d043490b07672efb2a1de766 [salt] => db5da1b71cdd4fe0ec89b24c [name] => [joined] => 2024-07-08 14:39:52 [group] => 1 [email] => saif@gmail.com ) 
    public function login(string $email = null, string $password = null)
    {                                
        if ( $this->exists( $email ) ) {
            if ( $this->data()->password == Hash::make( $password ) ) {
                $this->_is_logged_in = true;
                Session::put( $this->_session_name , $this->data()->email );                                                                    
                return true;
            }
        }
        return false;
    }
    
    public function is_logged_in() {
        return $this->_is_logged_in;
    }
    
    public function logout()
    {
        Session::delete( $this->_session_name );
    }
    
    public function exists( $email ) {
        $users = $this->load();
        foreach ($users as $user) {
            if ( $email == $user['email'] ) {
                $this->_data = (object) $user;
                return $this;
            }
        }
        return false;
    }
    
    public function get( $email ) {
        return $this->exists( $email );
    }
    
    public function getByID( $id ) {
        $users = $this->load();
        foreach ($users as $user) {
            if ( $id == $user['user_id'] ) {
                $this->_data = (object) $user;
                return $this;
            }
        }              
        return false;
    }    
    
    public function isAdmin() {
        if ( $this->_data->is_admin ) {
            return true;
        } else {
            return false;
        }
    }    
}