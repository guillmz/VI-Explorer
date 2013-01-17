<?PHP

class clBDPW {
	

  //- Information about the file password
  private $m_password=array();

  //- Information about the new password
  private $m_password_set=array();





  // -------------------------------------- //
  public function calcPassword($newPassword) {

    $out = array();

    $LVSR_ID = $this->getBlockIdByName('LVSR');
    $LIBN_ID = $this->getBlockIdByName('LIBN');

    $BDH__ID = $this->getBlockIdByName('BDHc');
    if ($BDH__ID < 0) $BDH__ID = $this->getBlockIdByName('BDHb');


    $LVSR_content = $this->getBlockContentById($LVSR_ID, 0, false);
    $LIBN_content = $this->getBlockContentById($LIBN_ID, 0, false); 
    $BDH__content = $this->getBlockContentById($BDH__ID);

    $md5_password = md5($newPassword,true);


    $LIBN_count = $LIBN_content->readInt(4);
    $LIBN_len = $LIBN_content->readInt(1);

    $new_content = str_repeat(chr(0),12);

    //-- Hash1:  MD5_PSW + LIBN + LVSR + 
    $md5_hash_1 = md5($md5_password . $LIBN_content->readStr($LIBN_len) . $LVSR_content->readStr() . $new_content ,true);
  

    $BDH__len = $BDH__content->readInt(4);
    $BDH__hash = md5($BDH__content->readStr($BDH__len), true);

    //-- Hash2:  Hash1 + BDHc
    $md5_hash_2 = md5($md5_hash_1 . $BDH__hash, true);


    $out['password']=$newPassword;
    $out['password_md5']=$md5_password;
    $out['hash_1']=$md5_hash_1;
    $out['hash_2']=$md5_hash_2;

    $this->m_password_set = $out;

  }



  // -------------------------------------- //
  public function writePassword() {

    $set_psw = $this->m_password_set;

    if (count($set_psw)>0)
    {
      $BDPW_ID = $this->getBlockIdByName('BDPW');

      $BDPW_content = $this->getBlockContentById($BDPW_ID, 0, false);

      $BDPW_content->writeStr($set_psw['password_md5']);
      $BDPW_content->writeStr($set_psw['hash_1']);
      $BDPW_content->writeStr($set_psw['hash_2']);
    }
    
  }


  // -------------------------------------- //
  private function readBDPW()
  {
    $BDPW_ID = $this->getBlockIdByName('BDPW');
    $block = $this->getBlockContentById($BDPW_ID, 0, false);

    $out = array();

    $out['password_md5'] = $block->readStr(16);
    $out['hash_1'] = $block->readStr(16);
    $out['hash_2'] = $block->readStr(16);

    $this->m_password = $out;

  }



  // -------------------------------------- //
  public function getPasswordHash($seperator='') {
    if (count($this->m_password)>0) return $this->toHex($this->m_password['password_md5'],$seperator);
    return '';
  }
}

?>