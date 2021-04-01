<?php
    class db{
        // Properties
        private $dbhost = 'aain9dw2210mx9.czi05dgbbsnf.ap-southeast-1.rds.amazonaws.com';
        private $dbuser = 'admin';
        private $dbpass = 'nyp12345';
        private $dbname = 'ImgCup2018';

        // Connect
        public function connect(){
            $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname";
            $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbConnection;
        }
    }