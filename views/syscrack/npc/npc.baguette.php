<?php

    use Framework\Syscrack\Game\Internet;
    use Framework\Syscrack\Game\NPC;

    if( isset( $npc ) == false )
    {

        $npc = new NPC();
    }

    if( isset( $internet ) == false )
    {

        $internet = new Internet();
    }

    $current_computer = $internet->getComputer( $ipaddress );
?>
<div class="row">
    <div class="col-md-12">
        <h5 style="color: #ababab" class="text-uppercase">
            <?php
                if( $npc->hasNPCFile( $current_computer->computerid ) )
                {

                    $schema = $npc->getNPCFile( $current_computer->computerid );

                    if( isset( $schema['name'] ) )
                    {

                        echo $schema['name'];
                    }
                }
                else
                {

                    echo 'Baguette.com';
                }
            ?>
        </h5>
        <p class="text-center">
            <img style="width: 100%; height: auto;" src="https://i.imgur.com/w3mCvS2.jpg">
        </p>
    </div>
</div>