<?php
class crypto {

	var $md5key;

	function crypto(){
		$this->md5key = "9ø206p3zw1eva156n5lpv";
	}

	function md5crypt($str){
		return $this->crypt_md5($str,$this->md5key);
	}

	function md5decrypt($str){
		return $this->decrypt_md5($str,$this->md5key);
	}

	function bytexor($a,$b,$l){
		$c="";
		for($i=0;$i<$l;$i++) {
			@$c.=$a{$i}^$b{$i};
		}
		return($c);
	}

	function binmd5($val){
		return(pack("H*",md5($val)));
	}

	function decrypt_md5($msg,$heslo){
		$key=$heslo;$sifra="";
		$key1=$this->binmd5($key);
		while($msg) {
			$m=substr($msg,0,16);
			$msg=substr($msg,16);
			$sifra.=$m=$this->bytexor($m,$key1,16);
			$key1=$this->binmd5($key.$key1.$m);
		}
		return($sifra);
	}

	function crypt_md5($msg,$heslo){
		$key=$heslo;$sifra="";
		$key1=$this->binmd5($key);
		while($msg) {
			$m=substr($msg,0,16);
			$msg=substr($msg,16);
			$sifra.=$this->bytexor($m,$key1,16);
			$key1=$this->binmd5($key.$key1.$m);
		}
		return($sifra);
	}

	function encrypt($str){
		return strrev(base64_encode($str));
	}

	function decrypt($str){
		return base64_decode(strrev($str)); 
	}

}

?>