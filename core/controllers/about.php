<?php

include_once __corePath.'controllers/header.php';
include_once __corePath.'controllers/footer.php';

class Page extends Controller
{ 
  public function prepare()
  {
  $header = new PageHeader($this->curpage, $this->db, $this->config);
  $footer = new PageFooter($this->curpage, $this->db, $this->config);
  
  $header->data['title'] = 'About';
  
  $this->data['header'] = $header->show();
  $this->data['footer'] = $footer->show();
  
  }


  public function show()
  {
  return $this->view(__corePath.'views/about.php', $this->data);
  }
}
?>