<?php
$text = 'For å melde deg på må du være innlogget på www.18bergen.org. Dersom du har glemt innloggingsopplysningene, kan du få opplysningene tilsendt herfra: http://www.18bergen.org/?sendlogininfo.

Tidligere speidere som ikke er registrert, må først registrere seg her: http://www.18bergen.org/registrering. De som ikke har egne mailadresser kan meldes på som gjester av andre påmeldte. ';

require_once('../../includes/classes/base.php');

base::sendContentType();
print base::makeHtmlUrls($text);

?>