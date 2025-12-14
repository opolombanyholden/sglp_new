<?php
if (extension_loaded('fileinfo')) {
    echo "✅ Extension fileinfo activée !";
} else {
    echo "❌ Extension fileinfo non disponible";
}
phpinfo();
?>