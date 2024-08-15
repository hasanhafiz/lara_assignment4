<?php 
namespace App;

class Transaction {
    private $_filepath = '',
            $_data = null,
            $_error = false;
            
    public function __construct()
    {
        $this->_filepath = "database/transactions.json";                                              
    }
    
    public function load()
    {
        if ( file_exists( $this->_filepath ) ) {
            return json_decode( file_get_contents( $this->_filepath ), true );
        } else {
            return [];
        }
    }
        
    public function create($fields = [])
    {
        $this->_error = false;
        $stored_data = $this->load();
        array_push( $stored_data, $fields );
        $write_into_file = file_put_contents( $this->_filepath, json_encode( $stored_data, JSON_PRETTY_PRINT ) );     
        if ( $write_into_file !== FALSE ) {
            $this->_error = false;
            // $this->_data = $this->get( $fields['id'] ); // get 
            return $this;
        } else {
            $this->_error = true;
        }    
    }
    
    public function data() {
        return $this->_data;
    }
    
    public function exists( $withdraw ) {
        $withdraws = $this->load();
        foreach ($withdraws as $withdraw) {
            if ( $withdraw == $withdraw['id'] ) {
                $this->_data = (object) $withdraw;
                return true;
            }
        }
        return false;
    }
    
    public function get( $id ) {
        return $this->exists( $id );
    }
    
    public function error() {
        return $this->_error;
    }
    
    public function getTransactionsByUser( $user_id ) {
        $all_transactions = $this->load();
        $filtered_data = [];
        foreach ($all_transactions as $transaction) {
            if ( $transaction['user_id'] == $user_id ) {
                $filtered_data[] = $transaction;
            }
        }
        return $filtered_data;
    }
    
    public function getCurrentBalance( $user_id ) {
        $deposited_amount = 0;
        $withdraw_amount = 0;
        $user_transactions = $this->getTransactionsByUser( $user_id );
        foreach ($user_transactions as $transaction) {
            if ( $transaction['transaction_type'] == TransactionType::DEPOSIT ) {
                $deposited_amount = $deposited_amount + $transaction['amount'];
            }
            if ( $transaction['transaction_type'] == TransactionType::WITHDRAW ) {
                $withdraw_amount = $withdraw_amount + $transaction['amount'];
            }            
        }
        return $deposited_amount - $withdraw_amount;
    }    
}