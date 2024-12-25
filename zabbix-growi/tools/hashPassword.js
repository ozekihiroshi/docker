const crypto = require('crypto');
const password = 'Ocusa87^^';
const seed = 'changeme';

const hashedPassword = crypto
    .createHash('sha256')
    .update(password + seed)
    .digest('hex');

console.log(hashedPassword);

