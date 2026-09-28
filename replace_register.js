const fs = require('fs');
let text = fs.readFileSync('includes/book_popup.php', 'utf8');
const newModal = fs.readFileSync('new_modal.html', 'utf8');

const splitText = text.split('<!-- Quick Register Modal -->');
if (splitText.length > 1) {
    const finalHTML = splitText[0] + newModal;
    fs.writeFileSync('includes/book_popup.php', finalHTML);
    console.log('done');
} else {
    console.log('could not find old modal marker');
}
