<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nowe zapytanie - AmperMaks</title>

    <style>
      body {
        margin: 0;
        padding: 0;
        background-color: #f4f6f8;
        font-family: Arial, Helvetica, sans-serif;
        color: #334155;
      }

      .wrapper {
        width: 100%;
        padding: 40px 15px;
        box-sizing: border-box;
      }

      .email-container {
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
      }

      .header {
        background-color: #0B1F3A;
        padding: 32px 40px;
        text-align: center;
      }

      .brand {
        margin: 0;
        color: #ffffff;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
      }

      .brand span {
        color: #F5A623;
      }

      .header-subtitle {
        margin: 8px 0 0 0;
        color: #cbd5e1;
        font-size: 14px;
      }

      .content {
        padding: 40px;
      }

      .badge {
        display: inline-block;
        background-color: #fff7e8;
        color: #b56d00;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 7px 12px;
        border-radius: 999px;
        margin-bottom: 18px;
      }

      h1 {
        margin: 0 0 10px 0;
        color: #0B1F3A;
        font-size: 26px;
        line-height: 1.3;
      }

      .intro {
        margin: 0 0 30px 0;
        color: #64748b;
        font-size: 15px;
        line-height: 1.7;
      }

      .details {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
      }

      .details td {
        padding: 14px 0;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
      }

      .details .label {
        width: 150px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
      }

      .details .value {
        color: #0B1F3A;
        font-size: 14px;
        font-weight: 700;
      }

      .details a {
        color: #0B1F3A;
        text-decoration: none;
      }

      .message-title {
        margin: 0 0 12px 0;
        color: #0B1F3A;
        font-size: 16px;
        font-weight: 700;
      }

      .message-box {
        background-color: #f8fafc;
        border-left: 4px solid #F5A623;
        border-radius: 8px;
        padding: 20px;
        color: #334155;
        font-size: 15px;
        line-height: 1.7;
      }

      .actions {
        text-align: center;
        margin-top: 32px;
      }

      .button {
        display: inline-block;
        background-color: #F5A623;
        color: #0B1F3A !important;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        padding: 14px 24px;
        border-radius: 8px;
      }

      .footer {
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 24px 40px;
        text-align: center;
      }

      .footer p {
        margin: 0;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.6;
      }

      @media only screen and (max-width: 600px) {
        .wrapper {
          padding: 15px;
        }

        .header,
        .content {
          padding: 28px 22px;
        }

        .footer {
          padding: 22px;
        }

        .details .label,
        .details .value {
          display: block;
          width: 100%;
        }

        .details .label {
          padding-bottom: 5px;
        }

        h1 {
          font-size: 22px;
        }
      }
    </style>
</head>

<body>

<div class="wrapper">

    <div class="email-container">


        <div class="header">
            <p class="brand">
                Amper<span>Maks</span>
            </p>

            <p class="header-subtitle">
                Instalacje elektryczne • Pomiary • Smart Home
            </p>
        </div>



        <div class="content">

            <span class="badge">
                Nowe zapytanie
            </span>

            <h1>
                Masz nową wiadomość ze strony
            </h1>

            <p class="intro">
                Klient wysłał formularz kontaktowy przez stronę AmperMaks.
                Poniżej znajdują się jego dane oraz treść zapytania.
            </p>



            <table class="details">

                <tr>
                    <td class="label">
                        Imię i nazwisko
                    </td>

                    <td class="value">
                        {{ $firstName }} {{ $lastName }}
                    </td>
                </tr>

                <tr>
                    <td class="label">
                        Adres e-mail
                    </td>

                    <td class="value">
                        <a href="mailto:{{ $email }}">
                            {{ $email }}
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="label">
                        Numer telefonu
                    </td>

                    <td class="value">
                        <a href="tel:{{ preg_replace('/\s+/', '', $phoneNumber) }}">
                            {{ $phoneNumber }}
                        </a>
                    </td>
                </tr>

            </table>



            <p class="message-title">
                Wiadomość klienta
            </p>

            <div class="message-box">
                {!! nl2br(e($contactMessage)) !!}
            </div>



            <div class="actions">

                <a
                        href="mailto:{{ $email }}"
                        class="button"
                >
                    Odpowiedz klientowi
                </a>

            </div>

        </div>



        <div class="footer">

            <p>
                Wiadomość została automatycznie wysłana
                z formularza kontaktowego na stronie AmperMaks.
            </p>

        </div>

    </div>

</div>

</body>
</html>