<?php

class Likes {

    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function addLike($id_origin, $id_dest) {
        $stmt = $this->db->prepare('INSERT INTO likes (id_origin, id_dest) VALUES (?, ?)');
        $stmt->execute([$id_origin, $id_dest]);
    }

    public function deleteLike($id_origin, $id_dest) {
        $stmt = $this->db->prepare('DELETE FROM likes WHERE id_origin = ? AND id_dest = ?');
        $stmt->execute([$id_origin, $id_dest]);
    }

    public function checkLike($id_origin, $id_dest) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE id_origin = ? AND id_dest = ?');
        $stmt->execute([$id_origin, $id_dest]);
        $count = $stmt->fetchColumn();
        if ($count == 1)
            return TRUE;
        else
            return FALSE;
    }

    public function checkBothLike($id_origin, $id_dest) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE id_origin = ? AND id_dest = ? OR id_origin = ? AND id_dest = ?');
        $stmt->execute([$id_origin, $id_dest, $id_dest, $id_origin]);
        $count = $stmt->fetchColumn();
        if ($count == 2)
            return TRUE;
        else
            return FALSE;
    }


}