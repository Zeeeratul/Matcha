<?php

class Profile {

    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function validatedProfile($id) {
    	$stmt = $this->db->prepare('SELECT COUNT(*) FROM profile WHERE id = ?');
    	$stmt->execute([$id]);
    	$count = $stmt->fetchColumn();
    	if ($count == 1)
    		return TRUE;
    	else
    		return FALSE;
    }

    public function updateProfile($id, $gender, $orientation, $birthdate, $bio, $char) {
        $stmt = $this->db->prepare('DELETE FROM profile WHERE id = ?');
        $stmt->execute([$id]);
        $stmt = $this->db->prepare('INSERT INTO profile (id, gender, orientation, birthdate, bio, characteristics) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, $gender, $orientation, $birthdate, $bio, $char]);
    }

    public function buildProfile($id, $gender, $orientation, $birthdate, $bio, $char) {
        $stmt = $this->db->prepare('INSERT INTO profile (id, popularity, gender, orientation, birthdate, bio, characteristics) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, 50, $gender, $orientation, $birthdate, $bio, $char]);
    }

    public function getAllInfoProfile($id) {
        $stmt = $this->db->prepare('SELECT * FROM profile WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function getProfileImage($id) {
        $stmt = $this->db->prepare('SELECT images FROM profile WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['images'];
    }

    public function updatePopularity($value, $id_user) {
        $stmt = $this->db->prepare('SELECT popularity FROM profile WHERE id = ?');
        $stmt->execute([$id_user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $popularity = $row['popularity'];
        $popularity = $popularity + $value;
        if ($popularity < 1)
            $popularity = 1;
        if ($popularity > 200)
            $popularity = 200;

        $stmt = $this->db->prepare('UPDATE profile SET popularity = ? WHERE id = ?');
        $stmt->execute([$popularity, $id_user]);
    }

    public function charGetUser($id, $char) {
        $stmt = $this->db->prepare('SELECT id FROM profile WHERE id != ? AND characteristics LIKE "%' . $char . '%"');
        $stmt->execute([$id]);
        $value = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $value;
    }

    public function putImageDb($name, $id) {
        $stmt = $this->db->prepare('SELECT images FROM profile WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //    if ($row['images'] != "")
    //        unlink('./ui/img_profil/' . $row['images']);

        $stmt = $this->db->prepare('UPDATE profile SET images = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    public function putImageOtherDb($name, $id) {
        $stmt = $this->db->prepare('SELECT images_other FROM profile WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $array = explode(" ", $row['images_other']);

        if (count($array) > 4)
        {
            unlink('./ui/img_profil/' . $array[3]);
            $array[3] = $name;        
            $name = implode(" ", $array);
        }     
        else
            $name = $name . " " . $row['images_other'];
        $stmt = $this->db->prepare('UPDATE profile SET images_other = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    public function getTags($idUser) {

        $stmt = $this->db->prepare('SELECT characteristics FROM profile WHERE id = ?');
        $stmt->execute([$idUser]);
        $row = $stmt->fetch();
        $tags = explode(' ',$row['characteristics']);
        return $tags;
    }

    public function tagFinding($tag, $id)
    {
        $tags = $this->getTags($id);
        if (in_array($tag, $tags))
            return (1);
        else
            return (0);
    }

    public function arrayTagFinding($tags, $id)
    {
        $nbtags = count($tags);
        $tagfound = 0;
        foreach ($tags as $tag)
        {
            if ($this->tagFinding($tag, $id))
            {
                $tagfound++;
            }
        }
        if ($nbtags == $tagfound)
            return (1);
        else
            return (0);
    }

    public function isBlock($userId, $blockUserId)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM black_list WHERE id_user = ? AND id_blocked = ?');
        $stmt->execute([$userId, $blockUserId]);
        $count = $stmt->fetchColumn();
        if ($count == 1)
            return TRUE;
        else
            return FALSE;
    }
}