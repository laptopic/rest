<?php


namespace application\models;


use application\core\Model;

class User extends Model
{

    public function createUser($data)
    {
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (fio, password, email, phone, active)
        VALUES( ".$this->db_field($data['fio']).", ".$this->db_field($password_hash). ", ".$this->db_field($data['email']).", ".$this->db_field($data['phone']).",
         ".$this->db_field($data['active']).")";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getUser($email){
        $email = htmlspecialchars(strip_tags($email));
        $sql = "SELECT id, fio, password, email, phone, active FROM users WHERE email =".$this->db_field($email);
        $row = $this->db->row($sql);

        if(!$row) return false;

        $data = [
            'id'        => $row['id'],
            'fio'       => $row['fio'],
            'password'  => $row['password'],
            'email'     => $row['email'],
            'phone'     => $row['phone'],
            'active'    => $row['active']
        ];

        return $data;
    }

     public function db_field($value, $maxlength = 0, $implode = false)
    {
        if(is_array($value)) {
            foreach($value as $k=>$v) $value[$k] = db_field($v, $maxlength);
            return $implode === false ? $value : implode($implode, $value);
        }
        else {
            $value = $maxlength ? addslashes(substr($value, 0, $maxlength)) : addslashes($value);
            return "'$value'";
        }
    }


}