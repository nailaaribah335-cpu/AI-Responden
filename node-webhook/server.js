require('dotenv').config();
const express = require('express');
const axios = require('axios');

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3000;
const LARAVEL_API_URL = process.env.LARAVEL_API_URL;

// Endpoint dinamis untuk menangkap chat masuk
app.post('/webhook/:platform', async (req, res) => {
    const platform = req.params.platform; 
    const payload = req.body;

    console.log(`⚡ [${platform.toUpperCase()}] Pesan baru masuk dari pelanggan!`);

    try {
        // Lempar data ke API Laravel secara instan
        await axios.post(`${LARAVEL_API_URL}/api/incoming-chat`, {
            platform: platform,
            data: payload
        });
        
        // Langsung balas 200 OK ke marketplace agar tidak timeout (< 3 detik)
        res.status(200).send('Webhook Received');
    } catch (error) {
        console.error('❌ Gagal meneruskan ke Laravel:', error.message);
        res.status(500).send('Error');
    }
});

app.listen(PORT, () => {
    console.log(`🚀 Webhook Gateway berjalan di http://localhost:${PORT}`);
});