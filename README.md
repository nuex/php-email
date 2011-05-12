# php-email

A simple wrapper for PHP mail that supports attachments and setting the attachment disposition (i.e. inline attachments).

## USAGE

    require_once('email.php');

    email::send(array(
      'subject' => 'Check out these pics!',
      'from' => 'John Smith <jsmith@email.com>',
      'to' => 'Jane Doe <jdoe@email.com>',
      'body' => "These pics are cool, I'm not even lying.",

      // Extra Headers
      'headers' => array(
        'X-Mailer' => 'PHP/' . phpversion()
      ),

      // Attachments 
      'attachments' => array(
        array(
          'path' => '/var/sites/mysite/public/pics/picture.jpg'
        ),
        array(
          'path' => '/var/sites/mysite/public/pics/picture2.jpg',
          'disposition' => 'inline'
        )
      )
    ));
