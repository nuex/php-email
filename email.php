<?php


// Send an email in PHP. Supports attachments and inline attachments.
//
// Copyright 2011 Chase Allen James <chaseajames@gmail.com>
//
// Usage:
//
//    email::send(array(
//      'subject' => 'Check out these pics!',
//      'from' => 'John Smith <jsmith@email.com>',
//      'to' => 'Jane Doe <jdoe@email.com>',
//      'body' => "These pics are cool, I'm not even lying.",
//
//      // Extra Headers
//      'headers' => array(
//        'X-Mailer' => 'PHP/' . phpversion()
//      ),
//
//      // Attachments 
//      'attachments' => array(
//        array(
//          'path' => '/var/sites/mysite/public/pics/picture.jpg'
//        ),
//        array(
//          'path' => '/var/sites/mysite/public/pics/picture2.jpg',
//          'disposition' => 'inline'
//        )
//      )
//    ));
class email {

  function send($opts) {
    $to = $opts['to'];

    // use blank subject if none provided
    $subject = isset($opts['subject']) ? $opts['subject'] : '';

    // use blank body if none provided
    $body = isset($opts['body']) ? $opts['body'] : '';

    $headers = array();

    if (isset($opts['from'])) {
      $headers['From'] = $opts['from'];
    }

    // merge in extra passed in headers
    if (isset($opts['headers'])) {
      $headers = array_merge($headers, $opts['headers']);
    }

    if (isset($opts['attachments'])) {
      $boundary = md5(time());
      $headers['MIME-Version'] = '1.0';
      $headers['Content-Type'] = "multipart/mixed;\n boundary=\"{$boundary}\"";
      $multipart = self::multipart_top($body, $boundary);
      $multipart .= self::attachments($opts['attachments'], $boundary);
      $multipart .= "--$boundary--";
    }

    $header_string = self::header_string($headers);
    $message = (isset($multipart) ? $multipart : $body);

    return mail($to, $subject, $message, $header_string);
  }


  // INTERNAL

  function multipart_top($body, $boundary) {
    return <<<txt

This is a message with multiple parts in MIME format.
--$boundary
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

$body
txt;
  }

  function attachments($attachments, $boundary) {
    $attachment_string = "\r\n";
    foreach ($attachments as $attachment) {
      $file_path = $attachment['path'];
      $disposition = (isset($attachment['disposition']) ? $attachment['disposition'] : 'attachment');
      $file = fopen($file_path, 'r');
      $read_data = fread($file, filesize($file_path));
      fclose($file);

      $data = chunk_split(base64_encode($read_data));

      $pathinfo = pathinfo($file_path);
      $filename = "{$pathinfo['filename']}.{$pathinfo['extension']}";
      $basename = $pathinfo['filename'];

      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $content_type = finfo_file($finfo, $file_path);
      finfo_close($finfo);

      $part = <<<txt
--$boundary
Content-Type: $content_type;
  name="$basename"
Content-Transfer-Encoding: base64
Content-Disposition: $disposition;
  filename="$filename"

$data
txt;
      $attachment_string .= $part;
    }
    return $attachment_string;
  }

  function header_string($header_array) {
    $str = '';
    foreach ($header_array as $k => $v) {
      $str .= "{$k}: {$v}\n";
    }
    return $str;
  }

}



?>
