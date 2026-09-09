// Test defaults for the wish form.
//
// Only ever applied when the server marks the block as prefillable, which it
// does outside production — see components/blocks/wish-form.antlers.html. The
// gate is deliberately server-side rather than a build flag: the compiled bundle
// is committed and deployed as-is, so a build-time flag would ship whatever was
// set on the machine that ran `npm run build`.
//
// Photo and consent aside, everything varies per load, so repeated submissions
// don't collapse onto one slug.

const FIRSTNAMES = ['Anna', 'Beat', 'Chiara', 'Dario', 'Elena', 'Fabio', 'Gina', 'Heinz', 'Irma', 'Jonas', 'Katja', 'Lukas'];
const LASTNAMES = ['Ammann', 'Brunner', 'Cavelti', 'Duss', 'Eggler', 'Frei', 'Gerber', 'Huber', 'Iten', 'Jaggi', 'Kunz', 'Lüthi'];

const WISHES = [
    'Ich wünsche mir für unseren Quartierverein neue Spielgeräte, damit die Kinder auch bei schlechtem Wetter draussen sein können.',
    'Mein Herzenswunsch ist ein Ausflug mit der ganzen Nachbarschaft, weil viele von uns kaum mehr aus dem Haus kommen.',
    'Ich wünsche mir einen Chorabend für unser Altersheim, damit die Bewohnerinnen und Bewohner wieder einmal zusammen singen können.',
    'Für den Turnverein wünsche ich mir neue Matten, weil die alten seit Jahren geflickt werden müssen.',
    'Ich wünsche mir ein Nachbarschaftsfest in unserer Strasse, damit sich die Leute hier endlich einmal richtig kennenlernen.',
];

const LINKS = [
    'https://www.example.com/verein',
    'https://www.example.com/projekt/spielplatz',
    'https://www.example.org/quartierverein',
    'https://www.example.net/unser-anliegen',
];

function pick(list) {
    return list[Math.floor(Math.random() * list.length)];
}

export function wishFormDefaults() {
    const firstname = pick(FIRSTNAMES);
    const lastname = pick(LASTNAMES);

    return {
        wish: pick(WISHES),
        link: pick(LINKS),
        firstname,
        lastname,
        street: 'Letzigraben 149',
        city: '8047 Zürich',
        // example.com is reserved, so a stray submission can never reach a real
        // inbox even if this ever ran somewhere it shouldn't.
        email: `${firstname}.${lastname}@example.com`.toLowerCase(),
        phone: '078 749 74 09',
        birthdate: '1977-04-07',
        accepts_terms: true,
        accepts_publication: true,
    };
}
