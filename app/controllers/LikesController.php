<?php

class LikesController extends Controller {

	public function likeUserAjax() {
		$User = new User($this->db);
		$Likes = new Likes($this->db);
		$Profile = new Profile($this->db);
		$Historical = new Historical($this->db);
		$Message = new Message($this->db);
		$username = $this->f3->get('POST.username');
		$bool = $this->f3->get('POST.bool');
		$id_origin = $this->f3->get("SESSION.uid");
		$id_dest = $User->getIdUser($username);
		if ($User->checkId($id_dest))
		{
			if ($bool == 1)
			{
				$Likes->addLike($id_origin, $id_dest);
				$Profile->updatePopularity(5, $id_dest);
				if ($Likes->checkBothLike($id_origin, $id_dest))
				{
					$Message->createConversation($id_origin, $id_dest);
					$Historical->addLiker($id_dest, $id_origin, 'Just like your profile in return (It s a match !)');
				}
				else
					$Historical->addLiker($id_dest, $id_origin, 'Just like your profile');
			}
			else
			{
				if ($Likes->checkBothLike($id_origin, $id_dest))
					$Historical->addDisliker($id_dest, $id_origin, 'Just dislike your profile, you are not matching anymore :(');
				$Likes->deleteLike($id_origin, $id_dest);
				$Profile->updatePopularity(-5, $id_dest);
			}
		}
		$this->f3->reroute('/profile/' . $username);
	}

}