<?php

echo "hi \n".get_current_user().'\n';
echo substr(get_current_user(),0,strpos(get_current_user(),'-'));
echo "hi";

?>