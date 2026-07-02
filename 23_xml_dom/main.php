<?php

// ============================================
// 23 - XML & DOM
// ============================================
// Topik: SimpleXML, DOMDocument, DOMNode,
//        XPath, XML ↔ Array conversion,
//        RSS/Atom parsing, XML validation
// ============================================

echo "==========================================\n";
echo "  XML & DOM\n";
echo "==========================================\n\n";

// ============================================
// BAGIAN A: SIMPLEXML
// ============================================
// SimpleXML memudahkan membaca/menulis XML

echo "--- 1. SIMPLEXML: READING XML ---\n\n";

// Parse XML dari string
$xmlString = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<bookstore>
    <book category="fiction">
        <title lang="en">The Great Gatsby</title>
        <author>F. Scott Fitzgerald</author>
        <year>1925</year>
        <price>10.99</price>
    </book>
    <book category="non-fiction">
        <title lang="en">A Brief History of Time</title>
        <author>Stephen Hawking</author>
        <year>1988</year>
        <price>15.99</price>
    </book>
    <book category="fiction">
        <title lang="en">1984</title>
        <author>George Orwell</author>
        <year>1949</year>
        <price>8.99</price>
    </book>
</bookstore>
XML;

$xml = simplexml_load_string($xmlString);

echo "  Root element: {$xml->getName()}\n";
echo "  Number of books: " . count($xml->book) . "\n\n";

// Akses data
foreach ($xml->book as $index => $book) {
    $title = (string) $book->title;
    $author = (string) $book->author;
    $year = (int) $book->year;
    $price = (float) $book->price;
    $category = (string) $book['category'];
    $lang = (string) $book->title['lang'];

    echo "  Book " . ($index + 1) . ":\n";
    echo "    Title: $title\n";
    echo "    Author: $author\n";
    echo "    Year: $year\n";
    echo "    Price: \$$price\n";
    echo "    Category: $category\n";
    echo "    Language: $lang\n\n";
}


echo "--- 2. SIMPLEXML: CREATING XML ---\n\n";

// Membuat XML baru
$root = new SimpleXMLElement('<employees/>');

// Tambah data
$emp1 = $root->addChild('employee');
$emp1->addChild('name', 'Budi Santoso');
$emp1->addChild('position', 'Senior Developer');
$emp1->addChild('salary', '15000000');
$emp1->addAttribute('id', '001');
$emp1->addAttribute('department', 'Engineering');

$emp2 = $root->addChild('employee');
$emp2->addChild('name', 'Andi Pratama');
$emp2->addChild('position', 'Product Manager');
$emp2->addChild('salary', '18000000');
$emp2->addAttribute('id', '002');
$emp2->addAttribute('department', 'Product');

// Format output
$dom = dom_import_simplexml($root)->ownerDocument;
$dom->formatOutput = true;
$createdXml = $dom->saveXML();

echo "  Created XML:\n";
echo "  " . str_replace("\n", "\n  ", $createdXml) . "\n";


echo "--- 3. SIMPLEXML: MODIFYING XML ---\n\n";

// Load XML yang ada
$xml = simplexml_load_string($xmlString);

// Tambah buku baru
$newBook = $xml->addChild('book');
$newBook->addChild('title', 'Clean Code');
$newBook->addChild('author', 'Robert C. Martin');
$newBook->addChild('year', '2008');
$newBook->addChild('price', '35.99');
$newBook->addAttribute('category', 'programming');
$newBook->title->addAttribute('lang', 'en');

// Ubah harga buku pertama
$xml->book[0]->price = 12.99;
echo "  Updated first book price: \${$xml->book[0]->price}\n";

// Hapus buku terakhir
$lastIndex = count($xml->book) - 1;
unset($xml->book[$lastIndex]);
echo "  Removed last book. Remaining: " . count($xml->book) . " books\n";

// Tampilkan hasil
$dom = dom_import_simplexml($xml)->ownerDocument;
$dom->formatOutput = true;
echo "\n  Modified XML:\n";
echo "  " . str_replace("\n", "\n  ", $dom->saveXML()) . "\n";


// ============================================
// BAGIAN B: DOMDOCUMENT
// ============================================
// DOMDocument memberikan kontrol lebih detail

echo "--- 4. DOMDOCUMENT: CREATING DOM ---\n\n";

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

// Root element
$catalog = $dom->appendChild($dom->createElement('catalog'));

// Tambah produk
$products = [
    ['name' => 'Laptop', 'price' => '15000000', 'stock' => '25'],
    ['name' => 'Mouse', 'price' => '150000', 'stock' => '100'],
    ['name' => 'Keyboard', 'price' => '750000', 'stock' => '50'],
];

foreach ($products as $id => $productData) {
    $product = $catalog->appendChild($dom->createElement('product'));
    $product->setAttribute('id', $id + 1);

    $product->appendChild($dom->createElement('name', $productData['name']));
    $product->appendChild($dom->createElement('price', $productData['price']));
    $product->appendChild($dom->createElement('stock', $productData['stock']));
}

echo "  Created DOM:\n";
echo "  " . str_replace("\n", "\n  ", $dom->saveXML()) . "\n";


echo "--- 5. DOMDOCUMENT: MODIFYING DOM ---\n\n";

// Load XML
$dom = new DOMDocument();
$dom->loadXML($xmlString);
$dom->formatOutput = true;

// Tambah element baru
$newBook = $dom->createElement('book');
$newBook->setAttribute('category', 'science');
$newBook->setAttribute('new', 'true');

$title = $dom->createElement('title', 'Cosmos');
$title->setAttribute('lang', 'en');
$newBook->appendChild($title);

$newBook->appendChild($dom->createElement('author', 'Carl Sagan'));
$newBook->appendChild($dom->createElement('year', '1980'));
$newBook->appendChild($dom->createElement('price', '12.99'));

$dom->getElementsByTagName('bookstore')->item(0)->appendChild($newBook);

// Ubah atribut
$firstBook = $dom->getElementsByTagName('book')->item(0);
$firstBook->setAttribute('category', 'classic-fiction');

echo "  Modified DOM:\n";
echo "  " . str_replace("\n", "\n  ", $dom->saveXML()) . "\n";


echo "--- 6. DOMNODE MANIPULATION ---\n\n";

$dom = new DOMDocument();
$dom->loadXML($xmlString);

$xpath = new DOMXPath($dom);

// Temukan semua book elements
$books = $dom->getElementsByTagName('book');
echo "  Found " . $books->length . " books\n\n";

// Clone node
$firstBook = $books->item(0);
$clonedBook = $firstBook->cloneNode(true);
$clonedBook->getElementsByTagName('title')->item(0)->nodeValue = 'Cloned: The Great Gatsby';

// Insert cloned book
$firstBook->parentNode->insertBefore($clonedBook, $firstBook->nextSibling);

echo "  After cloning:\n";
$newBooks = $dom->getElementsByTagName('book');
for ($i = 0; $i < $newBooks->length; $i++) {
    $book = $newBooks->item($i);
    $title = $book->getElementsByTagName('title')->item(0)->nodeValue;
    echo "    " . ($i + 1) . ". $title\n";
}

// Remove cloned book
$firstBook->parentNode->removeChild($clonedBook);
echo "\n  After removing clone: " . $dom->getElementsByTagName('book')->length . " books\n\n";


// ============================================
// BAGIAN C: XPATH QUERIES
// ============================================

echo "--- 7. XPATH QUERIES ---\n\n";

$dom = new DOMDocument();
$dom->loadXML($xmlString);
$xpath = new DOMXPath($dom);

// Register namespace
$xpath->registerNamespace('php', 'http://www.php.net/');
$xpath->registerNamespace('phpbook', 'http://www.php.net/books/');

// Berbagai queries
$queries = [
    'Semua buku' => '//book',
    'Buku fiction' => '//book[@category="fiction"]',
    'Judul semua buku' => '//book/title',
    'Harga buku non-fiction' => '//book[@category="non-fiction"]/price',
    'Buku tahun < 1950' => '//book[year < 1950]/title',
    'Buku harga > 10' => '//book[price > 10]/title',
    'Bahasa Inggris' => '//book/title[@lang="en"]',
    'Atribut category' => '//book/@category',
    'Buku pertama' => '//book[1]/title',
    'Buku terakhir' => '//book[last()]/title',
];

foreach ($queries as $description => $query) {
    $nodes = $xpath->query($query);
    $results = [];
    for ($i = 0; $i < $nodes->length; $i++) {
        $node = $nodes->item($i);
        $results[] = $node->nodeValue;
    }
    echo "  $description:\n";
    echo "    Query: $query\n";
    echo "    Results: " . implode(', ', $results) . "\n\n";
}


// ============================================
// BAGIAN D: XML ↔ ARRAY CONVERSION
// ============================================

echo "--- 8. XML TO ARRAY ---\n\n";

function xmlToArray(SimpleXMLElement $xml): array
{
    $result = [];

    // Attributes
    foreach ($xml->attributes() as $key => $value) {
        $result['@' . $key] = (string) $value;
    }

    // Children
    foreach ($xml->children() as $name => $child) {
        $childArray = xmlToArray($child);

        if (isset($result[$name])) {
            if (!isset($result[$name][0])) {
                $result[$name] = [$result[$name]];
            }
            $result[$name][] = $childArray;
        } else {
            $result[$name] = $childArray;
        }
    }

    // Text content
    $text = trim((string) $xml);
    if (!empty($text) && empty($result)) {
        return $text;
    }

    return $result;
}

$xml = simplexml_load_string($xmlString);
$array = xmlToArray($xml);

echo "  XML → Array:\n";
echo "  " . json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";


echo "--- 9. ARRAY TO XML ---\n\n";

function arrayToXml(array $data, string $rootName = 'root'): string
{
    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<{$rootName}/>");

    arrayToXmlHelper($data, $xml);

    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;
    return $dom->saveXML();
}

function arrayToXmlHelper(array $data, SimpleXMLElement $xml): void
{
    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $key = 'item';
        }

        if (is_array($value)) {
            $child = $xml->addChild($key);
            arrayToXmlHelper($value, $child);
        } else {
            $xml->addChild($key, htmlspecialchars((string) $value));
        }
    }
}

$products = [
    'product' => [
        ['name' => 'Laptop', 'price' => '15000000', 'category' => 'electronics'],
        ['name' => 'Buku PHP', 'price' => '150000', 'category' => 'books'],
        ['name' => 'Mouse', 'price' => '150000', 'category' => 'electronics'],
    ]
];

$xmlOutput = arrayToXml($products, 'catalog');
echo "  Array → XML:\n";
echo "  " . str_replace("\n", "\n  ", $xmlOutput) . "\n";


// ============================================
// BAGIAN E: RSS/ATOM FEED PARSING
// ============================================

echo "--- 10. RSS FEED PARSING ---\n\n";

$rssXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>PHP News</title>
        <link>https://news.php.net</link>
        <description>Latest PHP news and updates</description>
        <language>en-us</language>
        <pubDate>Mon, 01 Jan 2024 12:00:00 GMT</pubDate>

        <item>
            <title>PHP 8.3 Released</title>
            <link>https://news.php.net/php83</link>
            <description>PHP 8.3 has been released with many new features</description>
            <pubDate>Mon, 01 Jan 2024 12:00:00 GMT</pubDate>
            <category>Release</category>
            <guid>php83-release</guid>
        </item>

        <item>
            <title>New PHP RFC: Attributes 2.0</title>
            <link>https://news.php.net/rfc-attributes2</link>
            <description>A new RFC proposes enhanced attributes for PHP</description>
            <pubDate>Tue, 02 Jan 2024 10:00:00 GMT</pubDate>
            <category>RFC</category>
            <guid>rfc-attributes2</guid>
        </item>

        <item>
            <title>PHP Conference 2024 Announced</title>
            <link>https://news.php.net/conference2024</link>
            <description>PHP Conference 2024 will be held in Amsterdam</description>
            <pubDate>Wed, 03 Jan 2024 08:00:00 GMT</pubDate>
            <category>Event</category>
            <guid>php-conf-2024</guid>
        </item>
    </channel>
</rss>
XML;

function parseRssFeed(string $xml): array
{
    $feed = simplexml_load_string($xml);
    $channel = $feed->channel;

    $result = [
        'title' => (string) $channel->title,
        'link' => (string) $channel->link,
        'description' => (string) $channel->description,
        'language' => (string) $channel->language,
        'pubDate' => (string) $channel->pubDate,
        'items' => [],
    ];

    foreach ($channel->item as $item) {
        $result['items'][] = [
            'title' => (string) $item->title,
            'link' => (string) $item->link,
            'description' => (string) $item->description,
            'pubDate' => (string) $item->pubDate,
            'category' => (string) $item->category,
            'guid' => (string) $item->guid,
        ];
    }

    return $result;
}

$rssData = parseRssFeed($rssXml);

echo "  Feed: {$rssData['title']}\n";
echo "  Link: {$rssData['link']}\n";
echo "  Language: {$rssData['language']}\n";
echo "  Items: " . count($rssData['items']) . "\n\n";

foreach ($rssData['items'] as $index => $item) {
    echo "  " . ($index + 1) . ". {$item['title']}\n";
    echo "     Category: {$item['category']}\n";
    echo "     Date: {$item['pubDate']}\n";
    echo "     {$item['description']}\n\n";
}


echo "--- 11. ATOM FEED PARSING ---\n\n";

$atomXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>PHP Blog</title>
    <id>https://blog.example.com</id>
    <updated>2024-01-03T08:00:00Z</updated>
    <subtitle>Latest PHP articles and tutorials</subtitle>

    <entry>
        <title>Getting Started with PHP Fibers</title>
        <id>https://blog.example.com/php-fibers</id>
        <updated>2024-01-01T12:00:00Z</updated>
        <summary>Learn how to use PHP Fibers for concurrent programming</summary>
        <category term="tutorial" />
        <author>
            <name>Budi Santoso</name>
            <email>budi@example.com</email>
        </author>
        <link href="https://blog.example.com/php-fibers" rel="alternate" />
    </entry>

    <entry>
        <title>PHP 8.3 New Features</title>
        <id>https://blog.example.com/php83</id>
        <updated>2024-01-02T10:00:00Z</updated>
        <summary>Exploring the new features in PHP 8.3</summary>
        <category term="news" />
        <author>
            <name>Andi Pratama</name>
            <email>andi@example.com</email>
        </author>
        <link href="https://blog.example.com/php83" rel="alternate" />
    </entry>
</feed>
XML;

function parseAtomFeed(string $xml): array
{
    $feed = simplexml_load_string($xml);

    // Handle namespace
    $namespaces = $feed->getNamespaces(true);

    $result = [
        'title' => (string) $feed->title,
        'id' => (string) $feed->id,
        'updated' => (string) $feed->updated,
        'subtitle' => (string) $feed->subtitle,
        'entries' => [],
    ];

    foreach ($feed->entry as $entry) {
        $author = $entry->author;
        $result['entries'][] = [
            'title' => (string) $entry->title,
            'id' => (string) $entry->id,
            'updated' => (string) $entry->updated,
            'summary' => (string) $entry->summary,
            'category' => (string) $entry->category['term'],
            'author_name' => (string) $author->name,
            'author_email' => (string) $author->email,
            'link' => (string) $entry->link['href'],
        ];
    }

    return $result;
}

$atomData = parseAtomFeed($atomXml);

echo "  Feed: {$atomData['title']}\n";
echo "  ID: {$atomData['id']}\n";
echo "  Entries: " . count($atomData['entries']) . "\n\n";

foreach ($atomData['entries'] as $index => $entry) {
    echo "  " . ($index + 1) . ". {$entry['title']}\n";
    echo "     Author: {$entry['author_name']} ({$entry['author_email']})\n";
    echo "     Category: {$entry['category']}\n";
    echo "     {$entry['summary']}\n\n";
}


// ============================================
// BAGIAN F: XML VALIDATION
// ============================================

echo "--- 12. XML VALIDATION ---\n\n";

// XSD Schema untuk validasi
$schemaXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="library">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="book" maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="title" type="xs:string"/>
                            <xs:element name="author" type="xs:string"/>
                            <xs:element name="year" type="xs:integer"/>
                            <xs:element name="isbn" type="xs:string"/>
                        </xs:sequence>
                        <xs:attribute name="category" type="xs:string" use="required"/>
                    </xs:complexType>
                </xs:element>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
XML;

// Valid XML
$validXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<library>
    <book category="fiction">
        <title>The Great Gatsby</title>
        <author>F. Scott Fitzgerald</author>
        <year>1925</year>
        <isbn>978-0-7432-7356-5</isbn>
    </book>
    <book category="non-fiction">
        <title>Sapiens</title>
        <author>Yuval Noah Harari</author>
        <year>2011</year>
        <isbn>978-0-06-231609-7</isbn>
    </book>
</library>
XML;

// Invalid XML (missing required isbn)
$invalidXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<library>
    <book category="fiction">
        <title>The Great Gatsby</title>
        <author>F. Scott Fitzgerald</author>
        <year>1925</year>
    </book>
</library>
XML;

function validateXml(string $xml, string $schema): array
{
    $dom = new DOMDocument();

    // Load XML
    if (!$dom->loadXML($xml)) {
        return ['valid' => false, 'errors' => ['Failed to parse XML']];
    }

    // Load schema
    $schemaDom = new DOMDocument();
    if (!$schemaDom->loadXML($schema)) {
        return ['valid' => false, 'errors' => ['Failed to parse schema']];
    }

    // Validate
    $valid = $dom->schemaValidateSource($schemaDom);

    if ($valid) {
        return ['valid' => true, 'errors' => []];
    }

    // Get errors
    $errors = [];
    $errorCount = libxml_get_errors();
    foreach (libxml_get_errors() as $error) {
        $errors[] = trim($error->message);
    }
    libxml_clear_errors();

    return ['valid' => false, 'errors' => $errors];
}

// Test valid XML
$result = validateXml($validXml, $schemaXml);
echo "  Valid XML test:\n";
echo "    Valid: " . ($result['valid'] ? 'Ya' : 'Tidak') . "\n";
if (!empty($result['errors'])) {
    echo "    Errors: " . implode(', ', $result['errors']) . "\n";
}

// Test invalid XML
$result = validateXml($invalidXml, $schemaXml);
echo "\n  Invalid XML test:\n";
echo "    Valid: " . ($result['valid'] ? 'Ya' : 'Tidak') . "\n";
if (!empty($result['errors'])) {
    echo "    Errors:\n";
    foreach ($result['errors'] as $error) {
        echo "      - $error\n";
    }
}
echo "\n";


echo "--- 13. XML WELL-FORMEDNESS CHECK ---\n\n";

function checkXmlWellFormed(string $xml): array
{
    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $isValid = $dom->loadXML($xml);

    $errors = [];
    if (!$isValid) {
        foreach (libxml_get_errors() as $error) {
            $errors[] = [
                'message' => trim($error->message),
                'line' => $error->line,
                'column' => $error->column,
            ];
        }
    }

    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return [
        'well_formed' => $isValid,
        'errors' => $errors,
    ];
}

// Test
$malformedXml = <<<XML
<root>
    <item>Missing closing tag
    <item>Another item
</root>
XML;

$result = checkXmlWellFormed($malformedXml);
echo "  Well-formed check:\n";
echo "    Valid: " . ($result['well_formed'] ? 'Ya' : 'Tidak') . "\n";
foreach ($result['errors'] as $error) {
    echo "    Line {$error['line']}: {$error['message']}\n";
}
echo "\n";


// ============================================
// BAGIAN G: PRACTICAL EXAMPLE
// ============================================

echo "--- 14. PRACTICAL: CONFIGURATION FILE ---\n\n";

// Menggunakan XML sebagai config file
$configXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <app name="php-lambo" version="1.0.0" environment="development">
        <debug>true</debug>
        <timezone>Asia/Jakarta</timezone>
    </app>
    <database>
        <driver>mysql</driver>
        <host>localhost</host>
        <port>3306</port>
        <name>myapp_db</name>
        <user>root</user>
        <password></password>
    </database>
    <cache>
        <driver>file</driver>
        <directory>/tmp/cache</directory>
        <ttl>300</ttl>
    </cache>
    <logging>
        <level>debug</level>
        <handlers>
            <handler type="file">
                <path>/var/log/app.log</path>
            </handler>
            <handler type="stdout" />
        </handlers>
    </logging>
</configuration>
XML;

class XmlConfig
{
    private SimpleXMLElement $xml;
    private array $cache = [];

    public function __construct(string $xml)
    {
        $this->xml = simplexml_load_string($xml);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $parts = explode('.', $key);
        $current = $this->xml;

        foreach ($parts as $part) {
            if (isset($current->$part)) {
                $current = $current->$part;
            } else {
                return $default;
            }
        }

        $value = $this->castValue((string) $current);
        $this->cache[$key] = $value;

        return $value;
    }

    public function getArray(string $key): array
    {
        $parts = explode('.', $key);
        $current = $this->xml;

        foreach ($parts as $part) {
            if (isset($current->$part)) {
                $current = $current->$part;
            } else {
                return [];
            }
        }

        return $this->xmlToArray($current);
    }

    private function castValue(string $value): mixed
    {
        if (in_array(strtolower($value), ['true', 'yes', '1'])) {
            return true;
        }
        if (in_array(strtolower($value), ['false', 'no', '0'])) {
            return false;
        }
        if (is_numeric($value)) {
            return $value + 0;
        }
        return $value;
    }

    private function xmlToArray(SimpleXMLElement $xml): array
    {
        $result = [];
        foreach ($xml->children() as $name => $child) {
            if ($child->count() > 0) {
                $result[$name] = $this->xmlToArray($child);
            } else {
                $result[$name] = $this->castValue((string) $child);
            }
        }
        return $result;
    }
}

$config = new XmlConfig($configXml);

echo "  App name: " . $config->get('app.name') . "\n";
echo "  App version: " . $config->get('app.version') . "\n";
echo "  Debug: " . var_export($config->get('app.debug'), true) . "\n";
echo "  Timezone: " . $config->get('app.timezone') . "\n";
echo "  DB host: " . $config->get('database.host') . "\n";
echo "  DB port: " . $config->get('database.port') . "\n";
echo "  Cache driver: " . $config->get('cache.driver') . "\n";
echo "  Log level: " . $config->get('logging.level') . "\n";

echo "\n  Database config array:\n";
$dbConfig = $config->getArray('database');
echo "  " . json_encode($dbConfig, JSON_PRETTY_PRINT) . "\n\n";


echo "==========================================\n";
echo "  RINGKASAN\n";
echo "==========================================\n";
echo "\n";
echo "SIMPLEXML:\n";
echo "  - simplexml_load_string/loadXML: Parse XML\n";
echo "  - Akses: \$xml->child, \$xml['attr']\n";
echo "  - addChild(), addAttribute(): Tambah data\n";
echo "  - unset(): Hapus element\n";
echo "\n";
echo "DOMDOCUMENT:\n";
echo "  - createElement(), createTextNode(): Buat node\n";
echo "  - appendChild(), insertBefore(): Tambah node\n";
echo "  - removeChild(): Hapus node\n";
echo "  - cloneNode(): Clone node\n";
echo "\n";
echo "XPATH:\n";
echo "  - //element: Semua element\n";
echo "  - /path/to/element: Path spesifik\n";
echo "  - [@attr='value']: Filter by attribute\n";
echo "  - [condition]: Filter by condition\n";
echo "\n";
echo "CONVERSION:\n";
echo "  - XML → Array: Recursive traversal\n";
echo "  - Array → XML: Recursive creation\n";
echo "\n";
echo "PRACTICAL:\n";
echo "  - RSS/Atom feed parsing\n";
echo "  - XML validation (XSD)\n";
echo "  - XML as configuration file\n";
echo "\n";

echo "Selesai!\n";
