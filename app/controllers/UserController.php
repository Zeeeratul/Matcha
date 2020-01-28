 <?php

class UserController extends Controller {

    public function index() {
        $User = new User($this->db);
        $Profile = new Profile($this->db);
        $id = $this->f3->get('SESSION.uid');
        $info = $Profile->getAllInfoProfile($id);
        $age = $User->age($info['birthdate']);

        if (!$Profile->validatedProfile($id))
            $this->f3->set('view','user/logged.html');
        else
        {
            $result = $User->bestMatch($age);
            $result = $User->notMe($result, $id);
            $result = $User->genderSelect(array_values($result), $info['gender'], $info['orientation']);
            $i = 0;
            foreach ($result as $user_find) {
                if ($Profile->isBlock($this->f3->get('SESSION.uid'), $user_find['id']))
                    unset($result[$i]);
                $i++;
            }
            $result = array_values($result);
            $i = 0;
            foreach ($result as $user)
            {
                if ($i >= 5)
                    unset($result[$i]);
                $i++;
            }

            $i = 0;
            foreach ($result as $user) {
                $User = new User($this->db);
                $result[$i]['username'] = $User->getUserUsername($user['id']);
                $result[$i]['images'] = $Profile->getProfileImage($user['id']);
                $i++;
            }

            $this->f3->set('match', $result);
            $this->f3->set('view','user/logged.html');            
        }
    }

    public function sendEmail($to, $subject, $message) {
        $from = "celestdelahaye@gmail.com";
        $headers = "From:" . $from;
        mail($to,$subject,$message,$headers);
    }

    public function create() {
        if ($this->f3->get('POST.create'))
        {
            $username = $this->f3->get('POST.username');
            $password = password_hash($this->f3->get('POST.password'), PASSWORD_DEFAULT);
            $password_confirm = $this->f3->get('POST.password_confirm');

            //check if password is secure enough
            $uppercase = preg_match('@[A-Z]@', $password_confirm);
            $lowercase = preg_match('@[a-z]@', $password_confirm);
            $number    = preg_match('@[0-9]@', $password_confirm);

            $email = $this->f3->get('POST.email');
            $first_name = $this->f3->get('POST.first_name');
            $last_name = $this->f3->get('POST.last_name');

            if (empty($username) || empty($password) || empty($password_confirm) || empty($email) || empty($first_name) || empty($last_name))
                $this->f3->reroute('/create');
            else
            {
                $error = 0;
                $User = new User($this->db);
                $Pending_user = new PendingUsers($this->db);
                if ($this->f3->get('POST.password') !== $this->f3->get('POST.password_confirm'))
                {
                    $this->f3->set("error.passwordNotMatch" , 1);
                    $error = 1;
                }
                else
                    $this->f3->set("error.passwordNotMatch" , 0);
                if(!$uppercase || !$lowercase || !$number || strlen($password_confirm) < 8) {
                    $this->f3->set("error.passwordNotSecure" , 1);
                    $error = 1;
                }
                else
                    $this->f3->set("error.passwordNotSecure" , 0);
                if ($User->getUsername($username))
                {
                    $this->f3->set("error.usernameAlreadyUse" , 1);
                    $error = 1;
                }
                else
                    $this->f3->set("error.usernameAlreadyUse" , 0);
                if ($User->checkEmail($email))
                {
                    $this->f3->set("error.emailAlreadyUse" , 1);
                    $error = 1;
                }
                else
                    $this->f3->set("error.emailAlreadyUse" , 0);
                if ($error == 1)
                {
                    $this->f3->set("view" , "user/create.html");
                }
                else
                {
                    $User->addUserDb($username, $email, $password, $first_name, $last_name);
                    $token = $Pending_user->generateUniqueUrl($username);
                    $User->addOnlineTable($username);
                    $this->sendEmail($email, 'Validate your account !', 'Welcome to matcha click on this link to validate your account: https://celestindelahaye.ddns.net/matcha/validate/' . $token);
                    $this->f3->reroute('/');
                }
            }
        }
        else
            $this->f3->set('view', 'user/create.html');
    }

    public function login() {
        if ($this->f3->get('POST.login'))
        {
            $username = $this->f3->get('POST.username');
            $password = $this->f3->get('POST.password');
            if (empty($username) || empty($password))
                $this->f3->reroute('/login');
            else
            {
                $User = new User($this->db);
                $Profile = new Profile($this->db);
                $id_user = $User->getIdUser($username);
                if ($User->checkPassword($username, $password))
                {
                    $id_user = $User->getIdUser($username);
                    if (!$User->getUserValidation($id_user))
                        $this->f3->reroute('/login');
                    else if (!$Profile->validatedProfile($id_user))
                    {                    
                        $this->f3->set('SESSION.user', $username);
                        $this->f3->set('SESSION.uid', $id_user);
                        $this->f3->reroute('/buildProfile');
                    }
                    else
                    {
                        $this->f3->set('SESSION.user', $username);
                        $this->f3->set('SESSION.uid', $id_user);                        
                        $this->f3->reroute('/');
                    }
                }
                else
                    $this->f3->reroute('/');
            }      
        }
        else
            $this->f3->set('view','user/login.html');
    }

    public function logout() {
        $this->f3->clear('SESSION.user');
        $this->f3->clear('SESSION.uid');
        $this->f3->reroute('/login');
    }

    public function forgotten() {

        if ($this->f3->get('POST.forgotten'))
        {
            $email = $this->f3->get('POST.email');
            if (empty($email))
                $this->f3->reroute('/forgotten');
            else
            {
                $User = new User($this->db);
                $Pending_user = new PendingUsers($this->db);
                if ($User->checkEmail($email)) 
                {
                    $token = $Pending_user->generateUniqueUrl($email);
                    $this->sendEmail($email, 'Reset your password !', 'Reset your password, click on this link to reste your password: https://celestindelahaye.ddns.net/matcha/reset/' . $token);
                }
               $this->f3->reroute('/login');
            }
        }
        else
            $this->f3->set('view', 'user/forgotten.html');
    }

    public function reset() {
        $token = $this->f3->get('PARAMS.token');  
        $PendingUser = new PendingUsers($this->db);
        if ($PendingUser->checkTokenReset($token))
        {
            $this->f3->set('SESSION.token', $token);
            $this->f3->set('view', 'user/reset.html');
        }
        else
            $this->f3->reroute('/');
    }

    public function resetPassword() {
        if ($this->f3->get('POST.resetPassword'))
        {
            $token = $this->f3->get('SESSION.token');
            $password = password_hash($this->f3->get('POST.password'), PASSWORD_DEFAULT);
            $passwordConfirm = $this->f3->get('POST.password_confirm');

            //check if password is secure enough
            $uppercase = preg_match('@[A-Z]@', $passwordConfirm);
            $lowercase = preg_match('@[a-z]@', $passwordConfirm);
            $number    = preg_match('@[0-9]@', $passwordConfirm);

            if ($this->f3->get('POST.password') !== $passwordConfirm)
            {
                $this->f3->reroute('/reset/' . $token);
            }
            else if (!$uppercase || !$lowercase || !$number || strlen($passwordConfirm) < 8)
            {
                $this->f3->reroute('/reset/' . $token);
            }
            else
            {
                $PendingUser = new PendingUsers($this->db);
                $User = new User($this->db);
                $email = $PendingUser->getEmailFromToken($token);
                $User->changePassword($email, $password);
                $this->f3->clear('SESSION.token');
                $this->f3->reroute('/');
            }
        }
    }

    public function validateAccount() {
        $token = $this->f3->get('PARAMS.token');
        $PendingUser = new PendingUsers($this->db);
        if ($PendingUser->checkToken($token))
            $this->f3->reroute('/');
        else
            $this->f3->reroute('/create');
    }

    public function updatePassword() {
        if ($this->f3->get('POST.updatePassword'))
        {
            $User = new User($this->db);
            $username = $this->f3->get('SESSION.user');
            $currentpassword = $this->f3->get('POST.currentpassword');
            $newpassword = $this->f3->get('POST.newpassword');
            $newpassword_confirm = $this->f3->get('POST.newpassword_confirm');
            if ($User->checkPassword($username, $currentpassword))
            {
                if ($newpassword !== $newpassword_confirm || empty($newpassword))
                    $this->f3->reroute('/buildProfile');
                else
                {
                    $uppercase = preg_match('@[A-Z]@', $newpassword);
                    $lowercase = preg_match('@[a-z]@', $newpassword);
                    $number    = preg_match('@[0-9]@', $newpassword);
                    if(!$uppercase || !$lowercase || !$number || strlen($newpassword) < 8) {
                        $this->f3->reroute('/buildProfile');
                    }
                    $newpassword = password_hash($this->f3->get('POST.newpassword'), PASSWORD_DEFAULT);
                    $User->changePassword($User->getEmailUser($username), $newpassword);
                    $this->f3->reroute('/buildProfile');
                }
            }
            else
                $this->f3->reroute('/buildProfile');
        }
        else
            $this->f3->reroute('/buildProfile');
    }    

    public function updateEmail() {
        if ($this->f3->get('POST.updateEmail'))
        {
            $User = new User($this->db);
            $username = $this->f3->get('SESSION.user');
            $currentemail = $this->f3->get('POST.currentemail');
            $newemail = $this->f3->get('POST.newemail');
            $newemail_confirm = $this->f3->get('POST.newemail_confirm');
            $email = $User->getEmailUser($username);
            if ($email === $currentemail)
            {
                if ($newemail !== $newemail_confirm || empty($newemail))
                    $this->f3->reroute('/buildProfile');
                else
                {
                    $User->changeEmail($newemail, $username);
                    $this->f3->reroute('/buildProfile');
                }
            }
            else
                $this->f3->reroute('/buildProfile');
        }
        else
            $this->f3->reroute('/buildProfile');
    }

    public function updateFirstName() {
        if ($this->f3->get('POST.updateFirstName'))
        {
            $first_name = $this->f3->get('POST.FirstName');
            $User = new User($this->db);
            if (!empty($first_name))
            {
                $User->changeFirstName($first_name, $this->f3->get('SESSION.user'));
                $this->f3->reroute('/buildProfile');
            }
            else
                $this->f3->reroute('/buildProfile');
        }
    }    

    public function updateLastName() {
        if ($this->f3->get('POST.updateLastName'))
        {
            $last_name = $this->f3->get('POST.LastName');
            $User = new User($this->db);
            if (!empty($last_name))
            {
                $User->changeLastName($last_name, $this->f3->get('SESSION.user'));
                $this->f3->reroute('/buildProfile');
            }
            else
                $this->f3->reroute('/buildProfile');

        }
    }

    public function search() {
        $User = new User($this->db);
        $Profile = new Profile($this->db);

        $input = $this->f3->get('POST.search_input');
        if (!empty($input))
        {
            $users_finded = $User->searchUsers($input);
            if ($users_finded == $input)
                $this->f3->reroute('/profile/' . $input);
            else {

                $i = 0;
                foreach ($users_finded as $user_find) {
                    $users_finded[$i] = $Profile->getAllInfoProfile($user_find['id_users']);
                    $i++;
                }

                $i = 0;
                foreach ($users_finded as $user) {
                    $users_finded[$i]['username'] = $User->getUserUsername($user['id']);
                    $users_finded[$i]['images'] = $Profile->getProfileImage($user['id']);
                    $i++;
                }

                $i = 0;
                foreach ($users_finded as $user) {
                    if ($Profile->isBlock($this->f3->get('SESSION.uid'), $user['id']))
                        unset($users_finded[$i]);
                    $i++;
                }
                $this->f3->set('result', $users_finded);
            }
            if (empty($users_finded[0]))
                $this->f3->set('result', '');
            $this->f3->set('view', 'user/result.html');
        }
        else
            $this->f3->reroute('/');
    }

    public function signalAccount() {
        $User = new User($this->db);
        $id = $this->f3->get("SESSION.uid");
        $username = $this->f3->get("PARAMS.username");
        $id_fake = $User->getIdUser($username);
        $User->addFakeAccountDb($id, $id_fake);
        $this->f3->reroute('/');    
    }    

    public function blackListAccount() {
        $User = new User($this->db);
        $id = $this->f3->get("SESSION.uid");
        $username = $this->f3->get("PARAMS.username");
        $id_fake = $User->getIdUser($username);
        $User->blackListAccount($id, $id_fake);
        $this->f3->reroute('/');    
    }

    public function map()
    {
        $User = new User($this->db);
        if ($this->f3->get('POST.longitude') && $this->f3->get('POST.longitude')) {
            $lng = $this->f3->get('POST.longitude');
            $lat = $this->f3->get('POST.latitude');
            $User->setGeo($lat, $lng, $this->f3->get('SESSION.uid'));
        }
        $this->f3->reroute('/');
    }
}