let currentWord = '';
let currentDefinition = '';
let savedWordsVisible = false;

async function searchWord() {
  const word = document.getElementById('wordInput').value.trim();
  if (!word) return;

  const resultDiv = document.getElementById('result');
  resultDiv.innerHTML = 'Loading...';

  try {
    const res = await fetch(`api.php?word=${encodeURIComponent(word)}`);
    if (!res.ok) throw new Error("Word not found");

    const data = await res.json();
    const entry = data[0];

    const meaningsHtml = entry.meanings.map(m =>
      `<p><strong>${escapeHtml(m.partOfSpeech)}</strong>: ${escapeHtml(m.definitions[0].definition)}</p>`
    ).join("");

    resultDiv.innerHTML = `
      <h3>${escapeHtml(entry.word)}</h3>
      ${meaningsHtml}
    `;

    currentWord = entry.word;
    currentDefinition = entry.meanings[0]?.definitions[0]?.definition || '';
    document.getElementById('saveBtn').disabled = currentDefinition === '';

    if (currentDefinition) {
      await saveWord(currentWord, currentDefinition);
    }

  } catch (err) {
    resultDiv.innerHTML = `<p style="color:red;">Error: ${escapeHtml(err.message)}</p>`;
    currentWord = '';
    currentDefinition = '';
    document.getElementById('saveBtn').disabled = true;
  }
}

async function saveWord(word, definition) {
  try {
    const res = await fetch('save_word.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ word, definition })
    });

    const text = await res.text();
    if (!text) {
      console.warn('Empty response from save_word.php');
      return;
    }

    let data;
    try {
      data = JSON.parse(text);
    } catch (parseError) {
      console.error('Failed to parse JSON:', parseError, 'Response text:', text);
      return;
    }

    console.log('Save status:', data.status, data.message);
  } catch (e) {
    console.error('Save failed', e);
  }
}

async function saveCurrentWord() {
  if (!currentWord || !currentDefinition) {
    alert('No word or definition to save.');
    return;
  }

  try {
    const res = await fetch('save_word.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ word: currentWord, definition: currentDefinition })
    });
    const data = await res.json();

    if (data.status === 'success') {
      alert(`Word "${currentWord}" saved successfully!`);
    } else {
      alert(`Save failed: ${data.message || 'Unknown error'}`);
    }
  } catch (e) {
    alert('Save failed, see console.');
    console.error(e);
  }
}

async function toggleSavedWords() {
  const savedDiv = document.getElementById('savedWords');
  const toggleBtn = document.getElementById('toggleSavedBtn');

  if (savedWordsVisible) {
    savedDiv.innerHTML = '';
    if (toggleBtn) toggleBtn.textContent = 'Show Saved Words';
    savedWordsVisible = false;
  } else {
    if (toggleBtn) toggleBtn.textContent = 'Hide Saved Words';
    savedWordsVisible = true;
    savedDiv.innerHTML = 'Loading saved words...';

    try {
      const res = await fetch('get_saved_words.php');
      if (!res.ok) throw new Error('Failed to load saved words');

      const data = await res.json();

      if (!data.length) {
        savedDiv.innerHTML = '<p>No saved words yet.</p>';
        return;
      }

      const listHtml = data.map(item => `
        <div style="border-bottom: 1px solid #ccc; padding: 8px 0;">
          <strong>${escapeHtml(item.word)}</strong><br/>
          <em>${escapeHtml(item.definition)}</em><br/>
          <small>Saved at: ${escapeHtml(item.saved_at)}</small>
        </div>
      `).join('');

      savedDiv.innerHTML = `
        <div style="margin-bottom:10px;">
          <button onclick="deleteAllSavedWords()">Delete All Saved Words</button>
        </div>
        ${listHtml}
      `;

    } catch (err) {
      savedDiv.innerHTML = `<p style="color:red;">Error: ${escapeHtml(err.message)}</p>`;
    }
  }
}

async function deleteSavedWords() {
    if (!confirm("Are you sure you want to delete all saved words?")) return;
    try {
      const res = await fetch('delete_saved_words.php', { method: 'POST' });
      const data = await res.json();
      if (data.status === 'success') {
        alert("All saved words deleted.");
        document.getElementById('savedWords').innerHTML = '';
      } else {
        alert("Delete failed: " + data.message);
      }
    } catch (err) {
      alert("An error occurred while deleting saved words.");
      console.error(err);
    }
  }



function escapeHtml(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
