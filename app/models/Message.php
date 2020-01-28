<?php

class Message {

    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function addMessage($convId, $id_sender, $id_dest, $message) {
        $stmt = $this->db->prepare('INSERT INTO message (id_conversation, send_user_id, dest_user_id, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$convId, $id_sender, $id_dest, $message]);
    }

    public function getIdUserOfConv($id_conversation, $id_user) {
        $stmt = $this->db->prepare('SELECT send_user_id, dest_user_id FROM message WHERE id_conversation = ?');
        $stmt->execute([$id_conversation]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['send_user_id'] == $id_user)
            return $row['dest_user_id'];
        else
            return $row['send_user_id'];
    }

    public function getConversationsId($id) {
        $stmt = $this->db->prepare('SELECT id_conversation FROM message WHERE send_user_id = ? OR dest_user_id = ?');
        $stmt->execute([$id, $id]);
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_values(array_unique($row));
    }

    public function giveConvId() {
        $stmt = $this->db->prepare('SELECT MAX(id_conversation) FROM message');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $conversation_id = $row['MAX(id_conversation)'] + 1 ;
        return $conversation_id;
    }

    public function createConversation($id, $id2) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM message WHERE send_user_id = ? AND dest_user_id = ? OR send_user_id = ? AND dest_user_id = ?');
        $stmt->execute([$id, $id2, $id2, $id]);
        $count = $stmt->fetchColumn();
        if ($count == 0)
        {
            $conv_id = $this->giveConvId();
            $stmt = $this->db->prepare('INSERT INTO message (id_conversation, send_user_id, dest_user_id, message) VALUES (?, ?, ?, ?)');
            $stmt->execute([$conv_id, $id, $id2, 'Your conversation start here, Have fun :)']);
        }
    }

    public function checkConvId($id, $convId) {
        $stmt = $this->db->prepare('SELECT id_conversation FROM message WHERE id_conversation = ? AND (send_user_id = ? OR dest_user_id = ?)');
        $stmt->execute([$convId, $id, $id]);
        $row = $stmt->fetch();
        if ($row)
            return TRUE;
        else
            return FALSE;
    }

}