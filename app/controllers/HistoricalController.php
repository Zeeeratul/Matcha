<?php

class HistoricalController extends Controller {

    public function historical() {
        $Historical = new Historical($this->db);
        $id = $this->f3->get('SESSION.uid');
        $array = $Historical->getHistorical($id);
        $this->f3->set('info', $array);
        $this->f3->set('view', 'user/historical.html');
    }

    public function removeRow() {
        $Historical = new Historical($this->db);
        $id = $this->f3->get('POST.id');
    	$Historical->removeRow($id, $this->f3->get('SESSION.uid'));
        $this->f3->reroute('/historical');
    }

}