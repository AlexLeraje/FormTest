<?php

use Core\App;

class IndexModel extends Model
{
    public function getData() :array
    {
        $userData = $this->getUserData();
        return [
            'userName' => $userData['name'] ?? '',
        ];
    }

    private function getUserData() :array
    {
        if(!App::$User->id)
            return [];

        return App::$Data->selectFrom('Users')
            ->andWhere('id', '=', App::$User->id)
            ->execute()->get();
    }
}