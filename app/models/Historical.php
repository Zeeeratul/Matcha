<?php

class Historical {

    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function notificationMessage($id_other, $id_user, $convId) {
        $Profile = new Profile($this->db);
        $User = new User($this->db);
        if (!$Profile->isBlock($id_other, $id_user))
        {
            $username_visitor = $User->getUserUsername($id_user);
            $action = "Just send you a message";

            $stmt = $this->db->prepare('SELECT COUNT(*) FROM historical WHERE id_user = ? AND id_visitor = ? AND action = ?');
            $stmt->execute([$id_other, $id_user, $action]);
            $count = $stmt->fetchColumn();

            if ($count > 0)
            {
                $stmt = $this->db->prepare('UPDATE historical SET `date` = ?, bool = ? WHERE id_user = ? AND id_visitor = ? AND action = ?');
                $stmt->execute([date("Y-m-d\TH:i:sP"), 0, $id_other, $id_user, $action]);   
            }
            else
            {
      
                $url_conv = "../../printConversationAjax/" . $convId;
                $stmt = $this->db->prepare('INSERT INTO historical (id_user, id_visitor, username_visitor, action, url_conv, `date`) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$id_other, $id_user, $username_visitor, $action, $url_conv, date("Y-m-d\TH:i:sP")]);
            }
        }

    }

    public function addVisitor($id_profile, $id_visitor) {
        if ($id_profile != $id_visitor)
        {
            $Profile = new Profile($this->db);
            $User = new User($this->db);
            if (!$Profile->isBlock($id_profile, $id_visitor))
            {
                $username_visitor = $User->getUserUsername($id_visitor);
                $action = "Just visit your profile";

                $stmt = $this->db->prepare('SELECT COUNT(*) FROM historical WHERE id_user = ? AND id_visitor = ? AND action = ?');
                $stmt->execute([$id_profile, $id_visitor, $action]);
                $count = $stmt->fetchColumn();

                if ($count > 0)
                {
                    $stmt = $this->db->prepare('UPDATE historical SET `date` = ?, bool = ? WHERE id_user = ? AND id_visitor = ? AND action = ?');
                    $stmt->execute([date("Y-m-d\TH:i:sP"), 0, $id_profile, $id_visitor, $action]);   
                }
                else
                {
                    $stmt = $this->db->prepare('INSERT INTO historical (id_user, id_visitor, username_visitor, action, `date`) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$id_profile, $id_visitor, $username_visitor, $action, date("Y-m-d\TH:i:sP")]);   
                }
            }
        }
    }

    public function addLiker($id_profile, $id_visitor, $action) {
        if ($id_profile != $id_visitor)
        {
            $Profile = new Profile($this->db);
            $User = new User($this->db);
            if (!$Profile->isBlock($id_profile, $id_visitor))
            {
                $username_visitor = $User->getUserUsername($id_visitor);

                $stmt = $this->db->prepare('SELECT COUNT(*) FROM historical WHERE id_user = ? AND id_visitor = ? AND action = ?');
                $stmt->execute([$id_profile, $id_visitor, $action]);
                $count = $stmt->fetchColumn();

                if ($count > 0)
                {
                    $stmt = $this->db->prepare('UPDATE historical SET `date` = ?, bool = ? WHERE id_user = ? AND id_visitor = ? AND action = ?');
                    $stmt->execute([date("Y-m-d\TH:i:sP"), 0, $id_profile, $id_visitor, $action]);   
                }
                else
                {
                    $stmt = $this->db->prepare('INSERT INTO historical (id_user, id_visitor, username_visitor, action, `date`) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$id_profile, $id_visitor, $username_visitor, $action, date("Y-m-d\TH:i:sP")]);   
                }
            }
        }
    }   

    public function addDisliker($id_profile, $id_visitor, $action) {
        if ($id_profile != $id_visitor)
        {
            $Profile = new Profile($this->db);
            $User = new User($this->db);
            if (!$Profile->isBlock($id_profile, $id_visitor))
            {
                $username_visitor = $User->getUserUsername($id_visitor);

                $stmt = $this->db->prepare('SELECT COUNT(*) FROM historical WHERE id_user = ? AND id_visitor = ? AND action = ?');
                $stmt->execute([$id_profile, $id_visitor, $action]);
                $count = $stmt->fetchColumn();
                
                if ($count > 0)
                {
                    $stmt = $this->db->prepare('UPDATE historical SET `date` = ?, bool = ? WHERE id_user = ? AND id_visitor = ? AND action = ?');
                    $stmt->execute([date("Y-m-d\TH:i:sP"), 0, $id_profile, $id_visitor, $action]);   
                }
                else
                {
                    $stmt = $this->db->prepare('INSERT INTO historical (id_user, id_visitor, username_visitor, action, `date`) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$id_profile, $id_visitor, $username_visitor, $action, date("Y-m-d\TH:i:sP")]);   
                }
            }
        }
    }

    public function getHistorical($id_user) {
        $stmt = $this->db->prepare('SELECT * FROM historical WHERE id_user = ? ORDER BY `date` DESC');
        $stmt->execute([$id_user]);
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare('UPDATE historical SET bool = ? WhERE id_user = ?');
        $stmt->execute([1, $id_user]);
        return $row;
    }

    public function removeRow($id, $id_user) {
        $stmt = $this->db->prepare('DELETE FROM historical WHERE id = ? AND id_user = ?');
        $stmt->execute([$id, $id_user]);
    }

}