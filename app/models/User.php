<?php

class User {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function checkSession($id, $username) {
        if (empty($id) || empty($username))
            return FALSE;
        $stmt = $this->db->prepare('SELECT username FROM users WHERE id_users = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['username'] == $username)
            return TRUE;
        else
            return FALSE;
    }

    public function getAllUserInfo($id) {
        $stmt = $this->db->prepare('SELECT username, last_name, first_name FROM users WHERE id_users = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function getUsername($username) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();
        if ($count == 1)
            return TRUE;
        else
            return FALSE;
    }

    public function getIdUser($username) {
        $stmt = $this->db->prepare('SELECT id_users FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row['id_users'];
    }

    public function getEmailUser($username) {
        $stmt = $this->db->prepare('SELECT email FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row['email'];
    }

    public function getUserUsername($id_users) {
        $stmt = $this->db->prepare('SELECT username FROM users WHERE id_users = ?');
        $stmt->execute([$id_users]);
        $row = $stmt->fetch();
        return $row['username'];
    }

    public function getUserValidation($id_users) {
        $stmt = $this->db->prepare('SELECT validation FROM users WHERE id_users = ?');
        $stmt->execute([$id_users]);
        $row = $stmt->fetch();
        if ($row['validation'] == 1 && !empty($row['validation']))
            return TRUE;
        else
            return FALSE;
    }

    public function checkPassword($username, $password) {
        $stmt = $this->db->prepare('SELECT password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if(password_verify($password, $row['password']))
            return TRUE;
        else
            return FALSE;
    }

    public function checkEmail($email) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        if ($count == 1)
            return TRUE;
        else
            return FALSE;
    }

    public function checkId($id) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE id_users = ?');
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        if ($count == 1)
            return TRUE;
        else
            return FALSE;
    }

    public function validate($username) {
        $stmt = $this->db->prepare('UPDATE users SET validation = 1 WHERE username = ?');
        $stmt->execute([$username]);
    }

    public function changePassword($email, $password) {
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$password, $email]);
    }

    public function changeEmail($email, $username) {
        $stmt = $this->db->prepare('UPDATE users SET email = ? WHERE username = ?');
        $stmt->execute([$email, $username]);
    }

    public function changeFirstName($first_name, $username) {
        $stmt = $this->db->prepare('UPDATE users SET first_name = ? WHERE username = ?');
        $stmt->execute([$first_name, $username]);
    }

    public function changeLastName($last_name, $username) {
        $stmt = $this->db->prepare('UPDATE users SET last_name = ? WHERE username = ?');
        $stmt->execute([$last_name, $username]);
    }

    public function addUserDb($username, $email, $password, $first_name, $last_name) {
        $stmt = $this->db->prepare('INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $email, $password, $first_name, $last_name]);

        $id = $this->getIdUser($username);

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $apiKey = 'e14ed4abc6ab72';

        $apiQuery = 'https://ipinfo.io/%s/json?token=%s';

        $apiResult = file_get_contents(sprintf($apiQuery, $ip, $apiKey));
        $jsonResult = json_decode($apiResult);
        $city = $jsonResult->city;
        $region = $jsonResult->region;
        $country = $jsonResult->country;
        $lat = explode(',', $jsonResult->loc)[0];
        $lng = explode(',', $jsonResult->loc)[1];

        $stmt = $this->db->prepare('INSERT INTO geo (id_user, city, region, country, lat, lng) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, $city, $region, $country, $lat, $lng]);
    }

    public function searchUsers($input) {
        $stmt = $this->db->prepare('SELECT id_users, username, last_name, first_name FROM users WHERE username = ? OR last_name = ? OR first_name = ?');
        $stmt->execute([$input, $input, $input]);
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($input == $row['0']['username'])
            return $input;
        return $row;
    }

    public function addFakeAccountDb($id_user, $id_fake) {
        if ($id_user != $id_fake)
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM fake_account WHERE id_user = ? AND id_fake = ?');
            $stmt->execute([$id_user, $id_fake]);
            $count = $stmt->fetchColumn();
            if ($count == 0)
            {
                $stmt = $this->db->prepare('INSERT INTO fake_account (id_user, id_fake) VALUES (?, ?)');
                $stmt->execute([$id_user, $id_fake]);

                $Profile = new Profile($this->db);
                $Profile->updatePopularity(-10, $id_fake);
            }
        }
    }

    public function blackListAccount($id_user, $id_blocked) {
        if ($id_user != $id_blocked)
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM black_list WHERE id_user = ? AND id_blocked = ?');
            $stmt->execute([$id_user, $id_blocked]);
            $count = $stmt->fetchColumn();
            if ($count == 0)
            {
                $stmt = $this->db->prepare('INSERT INTO black_list (id_user, id_blocked) VALUES (?, ?)');
                $stmt->execute([$id_user, $id_blocked]);
            }
        }
    }

    public function addOnlineTable($username) {
        $id = $this->getIdUser($username);
        $stmt = $this->db->prepare('INSERT INTO online_user (id, log_time) VALUES (?, ?)');
        $stmt->execute([$id, date("Y-m-d\TH:i:sP")]);
    }

    public function updateOnlineTable($id) {
        $stmt = $this->db->prepare('UPDATE online_user SET log_time = ? WHERE id = ?');
        $stmt->execute([date("Y-m-d\TH:i:sP"), $id]);
    }

    public function getLastConnection($username) {
        $id = $this->getIdUser($username);
        $stmt = $this->db->prepare('SELECT log_time FROM online_user WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['log_time'];
    }

    public function connected($username) {
        $log_time = strtotime($this->getLastConnection($username));
        if ($_SERVER['REQUEST_TIME'] - $log_time < 300)
            return TRUE;
        else
            return FALSE;
    }

    public function setGeo($lat, $lng, $idUser)
    {
        $stmt = $this->db->prepare('UPDATE geo SET lat = ? WHERE id_user = ?');
        $stmt->execute([$lat, $idUser]);
        $stmt = $this->db->prepare('UPDATE geo SET lng = ? WHERE id_user = ?');
        $stmt->execute([$lng, $idUser]);
    }

    public function getLat($idUser)
    {
        $stmt = $this->db->prepare('SELECT lat FROM geo WHERE id_user = ?');
        $stmt->execute([$idUser]);
        $row = $stmt->fetch();
        return $row['lat'];
    }

    public function getLng($idUser)
    {
        $stmt = $this->db->prepare('SELECT lng FROM geo WHERE id_user = ?');
        $stmt->execute([$idUser]);
        $row = $stmt->fetch();
        return $row['lng'];
    }

    public function age($date)
    {
        $age = date('Y') - date('Y', strtotime($date));
        if (date('md') < date('md', strtotime($date))) {
            return $age - 1;
        }
        return $age;
    }

    public function bestMatch($age) {
        $agemin = $age - 10;
        $agemax = $age + 10;
        $date_min = date('Y-m-d', strtotime('today -' . $agemin . ' years'));
        $date_max = date('Y-m-d', strtotime('today -' . $agemax . ' years'));
        $stmt = $this->db->prepare('SELECT id, birthdate, popularity, gender, orientation, characteristics FROM profile WHERE (birthdate BETWEEN ? AND ?) ORDER BY popularity DESC');
        $stmt->execute([$date_max, $date_min]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function ageMinMax($agemin, $agemax) {
        $date_min = date('Y-m-d', strtotime('today -' . $agemin . ' years'));
        $date_max = date('Y-m-d', strtotime('today -' . $agemax . ' years'));

        $stmt = $this->db->prepare('SELECT id, birthdate, popularity, gender, orientation, characteristics FROM profile WHERE (birthdate BETWEEN ? AND ?) ORDER BY popularity DESC');
        $stmt->execute([$date_max, $date_min]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $i = 0;
        foreach ($result as $user) {
            $result[$i]['age'] = $this->age($user['birthdate']);
            $result[$i]['id'] = $user['id'];
            $i++;
        }

        return $result;
    }

    function publicScoreMinMax ($result, $scoreMin, $scoreMax) {
        $i = 0;
        foreach ($result as $user) {
            $result[$i]['username'] = $this->getUserUsername($user['id']);
            $result[$i]['id'] = $user['id'];
            $i++;
        }
        $j = 0;
        foreach ($result as $userSearch)
        {
            if ($userSearch['popularity'] < $scoreMin || $userSearch['popularity'] > $scoreMax)
                unset($result[$j]);
            $j++;
        }
        return $result;
    }
    
    public function getUserProfilImg($id_users) {
        $stmt = $this->db->prepare('SELECT images FROM profil WHERE id_users = ?');
        $stmt->execute([$id_users]);
        $row = $stmt->fetch();
        return $row['images'];
    }

    public function distance($lat1, $lng1, $lat2, $lng2) {
        if (($lat1 == $lat2) && ($lng1 == $lng2)) {
            return 0;
        }
        else {
            $theta = $lng1 - $lng2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;

            return ($miles * 1.609344);
        }
    }

    public function range($result, $range, $myLat, $myLng) {
        $i = 0;
        foreach ($result as $userSearch)
        {
            $id = $userSearch['id'];
            $lat = $this->getLat($id);
            $lng = $this->getLng($id);
            $distance = $this->distance($lat, $lng, $myLat, $myLng);
            $result[$i]['range'] = $distance;
            if ($distance > $range) {

                unset($result[$i]);
            }
            $i++;
        }
        return array_values($result);
    }

    public function notMe($result, $userId) {
        $i = 0;
        foreach ($result as $userSearch)
        {
            if ($userSearch['id'] == $userId) {
                unset($result[$i]);
            }
            $i++;
        }
        return array_values($result);
    }

    public function genderSelect($result, $gender, $orientation)
    {
        if ($orientation == 'gay') {
            $i = 0;
            foreach ($result as $userSearch) {
                if ($userSearch['orientation'] == 'straight')
                    unset($result[$i]);
                else if ($userSearch['gender'] != $gender)
                    unset($result[$i]);

                $i++;
            }
        }
        else if ($orientation == 'straight') {
            $i = 0;
            foreach ($result as $userSearch) {
                if ($userSearch['orientation'] == 'gay')
                    unset($result[$i]);
                else if ($userSearch['gender'] == $gender || $userSearch['gender'] == 'other')
                    unset($result[$i]);
                $i++;
            }
        }

        return array_values($result);
    }
}