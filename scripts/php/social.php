<?php


  $s = new SOCIAL($l);

  // this will handle all things read from and writing to the data source files
  class SOCIAL extends DB{


    var $dataPath = "";
    private $logs;


    // class constructor that gets called by default along with instantiation of class
    function __construct(LOGS $a) {
        $this->logs = $a;
    }





    // this function will write a new line of data to the mailing list registration file
    // a = the email address
    // b = the full name
    // which fields are required as an array
    function SOCIAL_ADDTOMAILINGLIST($a="",$b="",$c=[]){
      return $a;
    }




  }
?>
