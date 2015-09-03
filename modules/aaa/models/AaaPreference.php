<?php

namespace app\modules\circuits\models;

use Yii;
use app\models\Preference;


class AaaPreference extends Preference {

    //dominio padrao dos usuários logados via federação pela primeira vez
    const AAA_FEDERATION_DOMAIN = "aaa.federation.domain";

    //ativa o login via federação
    const AAA_FEDERATION_ENABLED = "aaa.federation.enabled";

    //grupo padrão dos usuários logados via federação pela primeira vez
    const AAA_FEDERATION_GROUP = "aaa.federation.group";

    static function getNames() {
        return array_merge(parent::getNames(),[
            static::AAA_FEDERATION_DOMAIN,
            static::AAA_FEDERATION_ENABLED,
            static::AAA_FEDERATION_GROUP,
        ]);
    }
}




