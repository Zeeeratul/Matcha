<?php


class MessageController extends Controller
{
	public function conversation() {
		$Message = new Message($this->db);
		$User = new User($this->db);
		$id = $this->f3->get('SESSION.uid');

		$ConvId = $Message->getConversationsId($id);
		foreach ($ConvId as $value) {
			$id_other = $Message->getIdUserOfConv($value['id_conversation'], $id);
			$conversationsList[] = array('username' => $User->getUserUsername($id_other), 'id_conversation' => $value['id_conversation']);
		}
		$this->f3->set('conversationsList', $conversationsList);
		$this->f3->set('view', 'user/conversationList.html');
	}

	public function sendMessage() {
		$User = new User($this->db);
		$Message = new Message($this->db);
		$Historical = new Historical($this->db);

		$convId = $this->f3->get('PARAMS.convId');
		$id = $this->f3->get('SESSION.uid');
		$id_other = $Message->getIdUserOfConv($convId, $id);
		$message = $this->f3->get('POST.message');

		if ($Message->checkConvId($id, $convId))
		{
			if (!empty($message))
			{
				$message = htmlspecialchars($message);
				$Message->addMessage($convId, $id, $id_other, $message);
				$Historical->notificationMessage($id_other, $id, $convId);
			}
			$this->f3->reroute('/printConversationAjax/' . $convId);
		}
		else
			$this->f3->reroute('/conversation');
	}

	function printConversationAjax() {
		$User = new User($this->db);
		$Profile = new Profile($this->db);
		$Message = new Message($this->db);
		$id = $this->f3->get('SESSION.uid');
		$convId = $this->f3->get('PARAMS.convId');
		if ($Message->checkConvId($id, $convId))
		{
			$id_dest = $Message->getIdUserOfConv($convId, $id);
			$ArrayInfoProfile = $User->getAllUserInfo($id_dest);

            $InfoArray = array('convId' => $convId, 'profile_image' => $Profile->getProfileImage($id_dest), 'first_name' => $ArrayInfoProfile['first_name'], 'last_name' => $ArrayInfoProfile['last_name'], 'username' =>  $ArrayInfoProfile['username'], 'id_dest' => $id_dest, 'id_sender' => $id_sender, 'conversation' => $allMessage);

			$this->f3->set('info', $InfoArray);
			$this->f3->set('view', 'user/conversationAjax.html');
		}
		else
			$this->f3->reroute('/conversation');
	}



}