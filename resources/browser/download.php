<?php

use Framework\Syscrack\Game\Computer;
use Framework\Syscrack\Game\Internet;
use Framework\Syscrack\Game\Schema;
use Framework\Syscrack\Game\Software;

if (isset($computer_controller) == false) {

    $computer_controller = new Computer();
}

if (isset($software) == false) {

    $software = new Software();
}

if (isset($internet) == false) {

    $internet = new Internet();
}

?>

<div class="row">
    <div class="col-sm-12">
        <h5 style="color: #ababab" class="text-uppercase">
            <?=$metadata->custom["name"]?>
        </h5>
        <p>
            Anything that you download here won't be logged!
        </p>

        <ul class="list-group">
            <?php

            $computeroftware = $computer_controller->getComputerSoftware($internet->getComputer($ipaddress)->computerid);

            if (empty($computeroftware)) {

                ?>
                <div class="panel panel-warning">
                    <div class="panel-body">
                        No softwares are currently available to download.. sorry!
                    </div>
                </div>
                <?php
            } else {

                foreach ($computeroftware as $key => $value) {

                    if ($software->softwareExists($value['softwareid']) == false) {

                        continue;
                    }

                    $value = $software->getSoftware($value['softwareid']);

                    if (empty($value->data)) {

                        continue;
                    }

                    $data = json_decode($value->data, true);

                    if (isset($data['allowanondownloads']) == false) {

                        continue;
                    }

                    ?>
                    <li class="list-group-item">
                        <a href="/game/internet/<?= $ipaddress ?>/anondownload/<?= $value->softwareid ?>"><?= $value->softwarename . $software->getSoftwareExtension($software->getSoftwareNameFromSoftwareID($value->softwareid)) . ' ' . $value->size . 'mb (' . $value->level . ')' ?></a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
    </div>
</div>