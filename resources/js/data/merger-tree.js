// The agency's hand-tuned layout: y is pixels down the chart, x is spread
// across whatever width the chart has. A founding needs no text; it gets one.
export const FINAL = 'weissenstein-2016';

export const banks = [
	{ id: 'selzach', year: 1902, x: 95, y: 50, name: 'Selzach', group: 'a' },
	{ id: 'lommiswil', year: 1932, x: 235, y: 135, name: 'Lommiswil', group: 'a' },
	{ id: 'bettlach', year: 1952, x: 365, y: 260, name: 'Bettlach', group: 'a' },
	{ id: 'selzach-lommiswil', year: 1997, x: 170, y: 435, name: 'Selzach-Lommiswil', group: 'a', text: 'Die Raiffeisenbanken Selzach und Lommiswil fusionieren zur Raiffeisenbank Selzach-Lommiswil.' },
	{ id: 'wandflue', year: 1999, x: 275, y: 540, name: 'Wandflue', group: 'a', text: 'Die Raiffeisenbanken Bettlach und Selzach-Lommiswil fusionieren zur Raiffeisenbank Wandflue.' },
	{ id: 'oberdorf', year: 1932, x: 470, y: 135, name: 'Oberdorf', group: 'b' },
	{ id: 'ruettenen', year: 1952, x: 585, y: 260, name: 'Rüttenen', group: 'b' },
	{ id: 'bellach', year: 1951, x: 720, y: 218, name: 'Bellach', group: 'b' },
	{ id: 'weissenstein-1998', year: 1998, x: 545, y: 490, name: 'Weissenstein', group: 'b', text: 'Die Raiffeisenbanken Oberdorf und Rüttenen fusionieren zur Raiffeisenbank Weissenstein.' },
	{ id: 'weissenstein-2004', year: 2004, x: 630, y: 685, name: 'Weissenstein', group: 'b', text: 'Die Raiffeisenbank Bellach fusioniert mit der Raiffeisenbank Weissenstein.' },
	{ id: 'solothurn', year: 1932, x: 820, y: 135, name: 'Solothurn', group: 'c' },
	{ id: 'feldbrunnen', year: 1957, x: 930, y: 308, name: 'Feldbrunnen', group: 'c' },
	{ id: 'riedholz', year: 1957, x: 1050, y: 308, name: 'Riedholz', group: 'c' },
	{ id: 'solothurn-1998', year: 1998, x: 855, y: 490, name: 'Solothurn', group: 'c', text: 'Die Raiffeisenbank Feldbrunnen fusioniert mit der Raiffeisenbank Solothurn.' },
	{ id: 'solothurn-1999', year: 1999, x: 930, y: 540, name: 'Solothurn', group: 'c', text: 'Die Raiffeisenbank Riedholz fusioniert mit der Raiffeisenbank Solothurn.' },
	{ id: 'guensberg', year: 1932, x: 1080, y: 135, name: 'Günsberg', group: 'd' },
	{ id: 'flumenthal-hubersdorf', year: 1932, x: 1130, y: 185, name: 'Flumenthal-Hubersdorf', group: 'd' },
	{ id: 'flumenthal-guensberg', year: 2000, x: 1055, y: 590, name: 'Flumenthal-Günsberg', group: 'd', text: 'Die Raiffeisenbanken Günsberg und Flumenthal-Hubersdorf fusionieren zur Raiffeisenbank Flumenthal-Günsberg.' },
	{ id: 'solothurn-2007', year: 2007, x: 930, y: 755, name: 'Solothurn', group: 'c', text: 'Die Raiffeisenbank Flumenthal-Günsberg fusioniert mit der Raiffeisenbank Solothurn.' },
	{ id: 'weissenstein-2016', year: 2016, x: 590, y: 867, name: 'Weissenstein', group: 'final', text: 'Die Raiffeisenbanken Wandflue, Weissenstein und Solothurn fusionieren zur heutigen Raiffeisenbank Weissenstein.' },
];

export const mergers = [
	['selzach', 'selzach-lommiswil'],
	['lommiswil', 'selzach-lommiswil'],
	['selzach-lommiswil', 'wandflue'],
	['bettlach', 'wandflue'],
	['oberdorf', 'weissenstein-1998'],
	['ruettenen', 'weissenstein-1998'],
	['weissenstein-1998', 'weissenstein-2004'],
	['bellach', 'weissenstein-2004'],
	['solothurn', 'solothurn-1998'],
	['feldbrunnen', 'solothurn-1998'],
	['solothurn-1998', 'solothurn-1999'],
	['riedholz', 'solothurn-1999'],
	['guensberg', 'flumenthal-guensberg'],
	['flumenthal-hubersdorf', 'flumenthal-guensberg'],
	['solothurn-1999', 'solothurn-2007'],
	['flumenthal-guensberg', 'solothurn-2007'],
	['wandflue', 'weissenstein-2016'],
	['weissenstein-2004', 'weissenstein-2016'],
	['solothurn-2007', 'weissenstein-2016'],
];
