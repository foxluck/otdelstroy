<?php
	interface IUser {
		public function getId();
		public function getLanguage();
		public function isAdmin();
		public function isPublic();
	}
?>