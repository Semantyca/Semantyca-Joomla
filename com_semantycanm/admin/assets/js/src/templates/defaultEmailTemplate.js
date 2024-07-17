export const defaultEmailTemplate = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our Newsletter</h1>
        </div>
        <div class="content">
            <p>Hello <%= name %>,</p>
            <p>This is a basic email template. You can customize this content to fit your needs.</p>
            <p>Here are some key points:</p>
            <ul>
                <li>Item 1</li>
                <li>Item 2</li>
                <li>Item 3</li>
            </ul>
            <p>Thank you for your attention!</p>
        </div>
        <div class="footer">
            <p>© 2024 Your Company. All rights reserved.</p>
            <p>You're receiving this email because you signed up for our newsletter.</p>
        </div>
    </div>
</body>
</html>
`;