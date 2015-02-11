<?php
//http://youtu.be/Aqyf2aYR1pQ
OEmbed::addProvider( new OEmbedApiProvider('http://www.youtube.com/oembed?url=:url&format=json', array(
    '~youtube\.com/watch.+v=[\w-]+&?~',
    '~youtu.be\/[\w-]+~'
    ) ) );
OEmbed::addProvider( new OEmbedApiProvider('http://www.flickr.com/services/oembed?url=:url&format=json', '~flickr\.com/photos/[-.\w@]+/\d+/?~' ) );

OEmbed::addProvider( new OEmbedApiProvider('http://lab.viddler.com/services/oembed?url=:url&format=json', '~\.viddler\.com/.+~' ) );

OEmbed::addProvider( new OEmbedApiProvider('http://qik.com/api/oembed.json?url=:url', '~qik\.com/.+~' ) );
OEmbed::addProvider( new OEmbedApiProvider('http://revision3.com/api/oembed?url=:url&format=json', '~\.revision3\.com/.+~' ) );
OEmbed::addProvider( new OEmbedApiProvider('http://www.hulu.com/api/oembed.json?url=:url', '~hulu\.com/watch/.+~' ) );
OEmbed::addProvider( new OEmbedApiProvider('http://www.vimeo.com/api/oembed.json?url=:url', '~vimeo\.com/.+~' ) );
OEmbed::addProvider( new OEmbedApiProvider('http://www.polleverywhere.com/services/oembed?url=:url&format=json', array(
    '~polleverywhere\.com/polls/.+~',
    '~polleverywhere\.com/multiple_choice_polls/.+~',
    '~polleverywhere\.com/free_text_polls/.+~'
) ) );
OEmbed::addProvider( new OEmbedApiProvider('http://api.smugmug.com/services/oembed?url=:url&format=json', '~\.smugmug\.com/.+~' ) );
OEmbed::addProvider( new OEmbedApiProvider('http://www.slideshare.net/api/oembed/2?url=:url&format=json', '~slideshare\.net/.+/.+~' ) );
OEmbed::addProvider( new OEmbedApiProvider('http://public-api.wordpress.com/oembed?url=:url&format=json', array(
    '~\.wordpress\.com/.+~',
    '~\.wp\.me/.+~'
) ) );










