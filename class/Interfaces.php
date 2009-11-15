<?php
namespace phpngb;

interface IUserProvider {
	static $current;
	static public function register($info);
}

interface IForumProvider {
}

interface IPostProvider {
}