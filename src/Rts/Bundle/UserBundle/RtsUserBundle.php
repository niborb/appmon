<?php

namespace Rts\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RtsUserBundle extends Bundle
{

    public function getParent()
    {
        return 'FOSUserBundle';
    }

}
