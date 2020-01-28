<?php

class PendingUsers {

    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function generateUniqueUrl($username) {
        $token = sha1(uniqid($username, true));
        $stmt = $this->db->prepare('INSERT INTO pending_users (username, token) VALUES (?, ?)');
        $stmt->execute([$username, $token]);
        return $token;
    }

    public function checkToken($token) {
        echo($token);
        if ($this->checkTokenReset($token))
        {
            $stmt = $this->db->prepare('SELECT username FROM pending_users WHERE token = ?');
            $stmt->execute([$token]);
            $row = $stmt->fetch();
            $stmt = $this->db->prepare('DELETE FROM pending_users WHERE token = ?');
            $stmt->execute([$token]);

            $user = new User($this->db);
            $user->validate($row['username']);
            return TRUE;
        }         
        else
            return FALSE;
    }
    
    public function checkTokenReset($token) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pending_users WHERE token = ?');
        $stmt->execute([$token]);
        $count = $stmt->fetchColumn();
        if ($count == 1)
            return TRUE;
        else
            return FALSE;        
    }

    public function getEmailFromToken($token) {
        $stmt = $this->db->prepare('SELECT username FROM pending_users WHERE token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        $stmt = $this->db->prepare('DELETE FROM pending_users WHERE token = ?');
        $stmt->execute([$token]);
        return $row['username'];
    }

}