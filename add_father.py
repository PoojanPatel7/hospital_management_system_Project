import re

with open('includes/book_popup.php', 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Add Father Name HTML
c = c.replace(
    '<label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Phone Number *</label>',
    '<label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Father\'s Name (Optional)</label>\n                <input type="text" id="qr-father" oninput="resetQRWarning()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/50 outline-none bg-slate-50 focus:bg-white transition shadow-sm">\n            </div>\n            <div>\n                <label class="block text-xs font-bold text-slate-700 mb-1 ml-1">Phone Number *</label>'
)

# 2. Add Father to handleQuickRegister and doQuickRegister
c = c.replace(
    "const phone = document.getElementById('qr-phone').value.trim();",
    "const phone = document.getElementById('qr-phone').value.trim();\n        const father = (document.getElementById('qr-father') ? document.getElementById('qr-father').value.trim() : '');"
)

# 3. Update matches logic
c = c.replace(
    "return nameMatch || phoneMatch;",
    "const fatherMatch = (father && p.father_name && p.father_name.toLowerCase() === father.toLowerCase());\n                return (nameMatch && (father === '' || fatherMatch)) || phoneMatch;"
)

# 4. Update fetch payload
c = c.replace(
    "body: JSON.stringify({ name: fname, surname: lname, phone: phone })",
    "body: JSON.stringify({ name: fname, surname: lname, phone: phone, father: father })"
)

# 5. Update returned object p
c = c.replace(
    "father_name: ''",
    "father_name: father"
)

with open('includes/book_popup.php', 'w', encoding='utf-8') as f:
    f.write(c)

print("done")
