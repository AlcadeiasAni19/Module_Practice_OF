<?php

namespace Modules\Authorization\app\Transformers;

use Modules\Authorization\app\Models\User;

class UserTransformer {
    public function transformFarmer (User $user) {
        return [
            'name' => $user->name,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'dob' => $user->dob,
            'district' => $user->district,
            'sub_district' => $user->sub_district
        ];
    }

    public function transformTrader (User $user) {
        return [
            'name' => $user->name,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'district' => $user->district,
            'sub_district' => $user->sub_district
        ];
    }

    public function transformCompany (User $user) {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'has_login_permission' => $user->has_login_permission
        ];
    }


}
