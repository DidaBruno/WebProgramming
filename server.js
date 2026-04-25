const express = require('express');
const app = express();
const fs = require('fs');
const path = require('path');

const PORT = process.env.PORT || 3000;

app.use(express.static('public'));

app.set('view engine', 'ejs');

app.get('/', (req, res) => {
    res.send('Hello from the Railway server');
});

app.get('/images', (req, res) => {
    const folderPath = path.join(__dirname, 'public', 'images');
    const files = fs.readdirSync(folderPath);

    const images = files
        .filter(file => file.endsWith('.jpg') || file.endsWith('.png'))
        .map((file, index) => ({
            url: `/images/${file}`,
            id: `image${index + 1}`,
            title: `Image ${index + 1}`
        }));

    res.render('images', { images });
});

app.listen(PORT, () => {
    console.log('Server started on port ${PORT}');
});
