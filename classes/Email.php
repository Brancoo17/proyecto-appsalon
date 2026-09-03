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
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'] ?? $_ENV['EMAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = ($_ENV['EMAIL_PORT'] == 465) ? 'ssl' : 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];

        // Configurar el contenido
        $remitente = $_ENV['EMAIL_USER'] ?? $_ENV['EMAIL_ADDRESS'] ?? $_ENV['EMAIL_ADDRES'] ?? 'tu_correo@gmail.com';

        $mail->setFrom($remitente, 'BarberShop');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Confirma tu Cuenta';

        // Contenido HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong></p>';
        $contenido .= '<p>Has creado una cuenta en AppSalon, confirma tu cuenta mediante el siguiente enlace:</p>';
        $contenido .= '<p>Presiona Acá: <a href="' . $_ENV['APP_URL'] . '/confirmar-cuenta?token=' . $this->token . '">Confirmar Cuenta</a></p>';
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
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'] ?? $_ENV['EMAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = ($_ENV['EMAIL_PORT'] == 465) ? 'ssl' : 'tls';
        $mail->Port = $_ENV['EMAIL_PORT'];

        // Configurar el contenido
        $remitente = $_ENV['EMAIL_USER'] ?? $_ENV['EMAIL_ADDRESS'] ?? $_ENV['EMAIL_ADDRES'] ?? 'tu_correo@gmail.com';

        $mail->setFrom($remitente, 'BarberShop');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Reestablece tu Password';

        // Contenido HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong></p>';
        $contenido .= '<p>Has solicitado reestablecer tu password, sigue el siguiente enlace para hacerlo:</p>';
        $contenido .= '<p>Presiona Acá: <a href="' . $_ENV['APP_URL'] . '/recuperar?token=' . $this->token . '">Reestablecer Password</a></p>';
        $contenido .= '<p>Si no solicitaste reestablecer tu password, puedes ignorar este mensaje</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar Mail
        $mail->send();

    }
}
