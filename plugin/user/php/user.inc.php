<?php
function isAdmin()
{
	return User::isConnected();
}