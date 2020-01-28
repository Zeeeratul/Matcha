<?php

class ProfileController extends Controller {

    public function displayProfile() {
        $username = $this->f3->get('PARAMS.username');
        $User = new User($this->db);
        $Likes = new Likes($this->db);
        $Profile = new Profile($this->db);
        $Historical = new Historical($this->db);
        if ($username == 'mine')
        {
            $historical = TRUE;
            $username = $this->f3->get('SESSION.user');
        }
        $id = $User->getIdUser($username);
        if (!empty($id))
        {
            $ArrayInfoUser = $User->getAllUserInfo($id);
            $ArrayInfoProfile = $Profile->getAllInfoProfile($id);
            $InfoArray = array('liked' => $Likes->checkLike($this->f3->get('SESSION.uid'), $id), 'username' => $ArrayInfoUser['username'], 'img_profile' => $ArrayInfoProfile['images'], 'img_other' => array_filter(explode(" ", $ArrayInfoProfile['images_other'])), 'firstname' => $ArrayInfoUser['first_name'], 'lastname' => $ArrayInfoUser['last_name'], 'gender' => $ArrayInfoProfile['gender'], 'orientation' => $ArrayInfoProfile['orientation'], 'bio' => $ArrayInfoProfile['bio'], 'char' => array_filter(explode('#', $ArrayInfoProfile['characteristics'])), 'checkLike' => $Likes->checkLike($id, $this->f3->get('SESSION.uid')), 'historical' => $historical, 'popularity' => $ArrayInfoProfile['popularity'], 'birthdate' => $ArrayInfoProfile['birthdate']);
            $Historical->addVisitor($id, $this->f3->get("SESSION.uid"));

            if ($User->connected($username))
                $this->f3->set('connected', 'Online');
            else
                $this->f3->set('connected', 'Last connection: ' . $User->getLastConnection($username));

            $this->f3->set('no_img_user', $Profile->getProfileImage($this->f3->get('SESSION.uid')));
            $this->f3->set('info', $InfoArray);
            $this->f3->set('view','user/userProfile.html');
        }
        else
            $this->f3->reroute('/');
    }

    public function buildProfile() {
        $Profile = new Profile($this->db);
        $User = new User($this->db);
        $id = $this->f3->get('SESSION.uid');

        if ($Profile->validatedProfile($id))
            $this->profileExists($id, $User, $Profile);
        else
            $this->newProfile($id, $User, $Profile);
    }

    public function profileExists($id, $User, $Profile) {
        if ($this->f3->get('POST.buildProfile'))
        {
            $gender = $this->f3->get('POST.gender');
            $orientation = $this->f3->get('POST.orientation');
            $birthdate = $this->f3->get('POST.birthdate');
            $bio = $this->f3->get('POST.bio');
            $char = $this->f3->get('POST.char');
            $Profile->updateProfile($id, $gender, $orientation, $birthdate, $bio, $char);
            $this->f3->reroute('/');
        }
        else
        {
            $arrayInfo = $Profile->getAllInfoProfile($id);
            $arrayUser = $User->getAllUserInfo($id);
            $this->f3->set('first_name', $arrayUser['first_name']);
            $this->f3->set('last_name', $arrayUser['last_name']);
            $this->f3->set($arrayInfo['gender'], 'selected=""');
            $this->f3->set($arrayInfo['orientation'], 'selected=""');
            $this->f3->set('birthdate', $arrayInfo['birthdate']);
            $this->f3->set('bio', $arrayInfo['bio']);
            $this->f3->set('characteristics', $arrayInfo['characteristics']);


            $this->f3->set('view', 'user/buildProfile.html');
        }
    }

    public function newProfile($id, $User, $Profile) {
        if ($this->f3->get('POST.buildProfile'))
        {
            $gender = $this->f3->get('POST.gender');
            $orientation = $this->f3->get('POST.orientation');
            $birthdate = $this->f3->get('POST.birthdate');
            $bio = $this->f3->get('POST.bio');
            $char = $this->f3->get('POST.char');
            $Profile->buildProfile($id, $gender, $orientation, $birthdate, $bio, $char);
            $this->f3->reroute('/');
        }
        else
        {
            $this->f3->set('display_none', 'true');
            $this->f3->set('bi', 'selected=""');
            $this->f3->set('bio', 'Introduce Yourself !');
            $this->f3->set('characteristics', 'Write Your favorite things separate with a space (#fun #sport #party)');
            $this->f3->set('view', 'user/buildProfile.html');
        }
    }

    public function searchSoulMate() {
        $User = new User($this->db);
        $Profile = new Profile($this->db);

        if ($this->f3->get('POST.public_score_max') && $this->f3->get('POST.age_min') && $this->f3->get('POST.age_max') && $this->f3->get('POST.public_score_min') && $this->f3->get('POST.searchSoulMate') == 'Find Matchs') {
            $score_max = $this->f3->get('POST.public_score_max');
            $score_min = $this->f3->get('POST.public_score_min');
            $age_min = $this->f3->get('POST.age_min');
            $age_max = $this->f3->get('POST.age_max');
            $range = $this->f3->get('POST.km');
            $tags = array_filter(explode(' ', $this->f3->get('POST.tag')));
            $lat = $User->getLat($this->f3->get('SESSION.uid'));
            $lng = $User->getLng($this->f3->get('SESSION.uid'));

            $usersFind = $User->ageMinMax($age_min, $age_max);
            $resultNoRange = $User->publicScoreMinMax($usersFind, $score_min, $score_max);
            $result = $User->range(array_values($resultNoRange), $range, $lat, $lng);
            $result = $User->notMe($result, $this->f3->get('SESSION.uid'));
            $allInfo = $Profile->getAllInfoProfile($this->f3->get('SESSION.uid'));

            if ($tags) {
                $i = 0;
                foreach ($result as $userFind) {
                    if (!$Profile->arrayTagFinding($tags, $userFind['id']))
                        unset($result[$i]);
                    $i++;
                }
            }

            $result = $User->genderSelect(array_values($result), $allInfo['gender'], $allInfo['orientation']);

            array_values($result);
            $i = 0;
            foreach ($result as $user_find) {
                if ($Profile->isBlock($this->f3->get('SESSION.uid'), $user_find['id']))
                    unset($result[$i]);
                $i++;
            }


            if ($this->f3->get('POST.order')) {
                $order = $this->f3->get('POST.order');
                if ($order == 'geo')
                {
                    function custom_sort_range($a,$b) {
                        return $a['range']>$b['range'];
                    }
                    usort($result, "custom_sort_range");
                }
                else if ($order == 'birthday')
                {
                    function custom_sort_age($a,$b) {
                        return $a['age']>$b['age'];
                    }
                    usort($result, "custom_sort_age");
                }
            }

            $i = 0;
            foreach ($result as $user) {
                $User = new User($this->db);
                $result[$i]['username'] = $User->getUserUsername($user['id']);
                $result[$i]['images'] = $Profile->getProfileImage($user['id']);
                $i++;
            }

            $this->f3->set('result', array_filter($result));
            $pagination['total_users'] = count($result);
            $pagination['nb_page'] = $pagination['total_users'] / 10;

            $this->f3->set('count', $pagination);
            $this->f3->set('view', 'user/result.html');
        }
        else
            $this->f3->set('view', 'user/search.html');
    }

    public function giveCharUser() {
        $Profile = new Profile($this->db);
        $User = new User($this->db);
        $id = $this->f3->get('SESSION.uid');
        $char = $this->f3->get('PARAMS.char');
        $idArray = $Profile->charGetUser($id, $char);
        foreach ($idArray as $value) {
            $userArray[] = array('username' => $User->getUserUsername($value['id']), 'images' => $Profile->getProfileImage($value['id']));
        }
        $this->f3->set('result', $userArray);
        $this->f3->set('view', 'user/result.html');

    }

    public function check_img($file)
    {
        $imageFileType = strtolower(pathinfo($file['File']['name'], PATHINFO_EXTENSION));
        if ($file["File"]["size"] > 2000000)
        {
            $error = "Image size is bigger than 2mo";
        }
        else if ($imageFileType === "png")
        {
            if (!$img = @imagecreatefrompng($file["File"]["tmp_name"]))
                $error = "Bad png";
            else
                $error = "png";
        }
        else if ($imageFileType === "jpg" || $imageFileType === "jpeg")
        {
            if (!$img = @imagecreatefromjpeg($file["File"]["tmp_name"]))
                $error = "Bad pjg";
            else
                $error = "jpg";
        }
        else if ($imageFileType === "gif")
        {
            if (!$img = @imagecreatefromgif($file["File"]["tmp_name"]))
                $error = "Bad gif";
            else
                $error = "gif";
        }
        else
            $error = "Format supported jpg, jpeg, png, gif";
        return $error;
    }

    public function uploadPhotoProfile() {
        $Profile = new Profile($this->db);
        $flag = $this->check_img($_FILES);
        if ($flag == "png")
            $name = ".png";
        else if ($flag == "jpg")
            $name = ".jpg";
        else if ($flag == "gif")
            $name = ".gif";
        else
            $this->f3->reroute('/');

        $name = uniqid() . $name;
        move_uploaded_file($_FILES["File"]["tmp_name"], "./ui/img_profil/" . $name);
        if ($this->f3->get('POST.UploadProfile'))
            $Profile->putImageDb($name, $this->f3->get('SESSION.uid'));
        else
            $Profile->putImageOtherDb($name, $this->f3->get('SESSION.uid'));
        $this->f3->reroute('/profile/mine');
    }


}