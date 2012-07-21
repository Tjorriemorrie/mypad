<?php

namespace My\PadBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;

class SongType
{
	public function __construct(FormBuilder $fb, array $otpions)
	{

	}


	public function getName()
	{
		return 'song';
	}
}
