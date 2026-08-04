<?php 

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public string $email;
    public string $nombre;
    public string $token;

    public function __construct(string $email, string $nombre, string $token) {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {

        // Crear el objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'bc69bc3b5a9557';
        $mail->Password = 'af1c3fa2ceb46c';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 2525;

        // Configurar el contenido
        $mail->setFrom('correo@appsalon.com');
        $mail->addAddress('correo@appsalon.com', 'AppSalon.com');
        $mail->Subject = 'Confirma tu Cuenta';

        // Contenido HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong></p>';
        $contenido .= '<p>Has creado una cuenta en AppSalon, confirma tu cuenta mediante el siguiente enlace:</p>';
        $contenido .= '<p>Presiona Acá: <a href="http://localhost:3000/confirmar-cuenta?token=' . $this->token . '">Confirmar Cuenta</a></p>';
        $contenido .= '<p>Si no has creado esta cuenta, puedes ignorar este mensaje</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar Mail
        $mail->send();
        
    }

    // Enviar instrucciones para recuperar password
    public function enviarInstrucciones() {

        // Crear el objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'bc69bc3b5a9557';
        $mail->Password = 'af1c3fa2ceb46c';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 2525;

        // Configurar el contenido
        $mail->setFrom('correo@appsalon.com');
        $mail->addAddress('correo@appsalon.com', 'AppSalon.com');
        $mail->Subject = 'Reestablece tu Password';

        // Contenido HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong></p>';
        $contenido .= '<p>Has solicitado reestablecer tu password, sigue el siguiente enlace para hacerlo:</p>';
        $contenido .= '<p>Presiona Acá: <a href="http://localhost:3000/recuperar?token=' . $this->token . '">Reestablecer Password</a></p>';
        $contenido .= '<p>Si no solicitaste reestablecer tu password, puedes ignorar este mensaje</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar Mail
        $mail->send();

    }
}
