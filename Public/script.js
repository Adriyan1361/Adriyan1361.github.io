function uploadFile() {
  const fileInput = document.getElementById("fileInput");
  const output = document.getElementById("output");

  if (!fileInput.files.length) {
    output.textContent = "No file selected.";
    return;
  }

  const file = fileInput.files[0];
  const reader = new FileReader();

  reader.onload = async function () {
    const content = reader.result;

    output.textContent = "Uploading...\n";

    const response = await fetch("/upload", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ content })
    });

    const result = await response.json();
    output.textContent += result.message;
  };

  reader.readAsText(file);
}
