<?php

namespace JayaInstitute;

class Hash {

	public function make($value='')
	{
		return sha1($value);
	}

}