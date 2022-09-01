<?php

namespace app\common;

class Hash
{
    //计算、验证哈希值
    private $salt;

    public function __construct()
    {
        $this->salt = md5(openssl_random_pseudo_bytes(32));
    }

    public function compute($data): array
    {
        return [
            "passwd" => md5($data . $this->salt),
            "salt" => $this->salt
        ];
    }

    public function verify($hash, $data, $salt): bool
    {
        //防范时序攻击
        if (hash_equals($hash, md5($data . $salt))) {
            return true;
        } else {
            return false;
        }
    }
}