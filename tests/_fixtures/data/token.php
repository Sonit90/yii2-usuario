<?php

use Da\User\Model\Token;

$time = time();

return [
    'confirmation' => [
        'useId' => 2,
        'code' => 'NO2aCmBIjFQX624xmAc3VBu7Th3NJoa6',
        'type' => Token::TYPE_CONFIRMATION,
        'createAt' => $time,
    ],
    'expireConfirmation' => [
        'useId' => 3,
        'code' => 'qxYa315rqRgCOjYGk82GFHMEAV3T82AX',
        'type' => Token::TYPE_CONFIRMATION,
        'createAt' => $time - 86401,
    ],
    'expireRecovery' => [
        'useId' => 5,
        'code' => 'a5839d0e73b9c525942c2f59e88c1aaf',
        'type' => Token::TYPE_RECOVERY,
        'createAt' => $time - 21601,
    ],
    'recovery' => [
        'useId' => 6,
        'code' => '6f5d0dad53ef73e6ba6f01a441c0e602',
        'type' => Token::TYPE_RECOVERY,
        'createAt' => $time,
    ],
];
