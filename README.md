# Poirot\PathUri

Powerful while Remaining Simple to use library to ease URL parsing and editing in PHP.

## General Usage

__PathJoinedUri__

Basic Path Interfaces iPathJoinedUri

```php
### Build object from construct ---------------------------------------------------
$joinedPath = new PathJoinUri([
    # (!) when you pass a path on construct
    #     you have to set separator
    'path' => '/var/www/html/',                        # <-----
                                                       #      |
    # path also can build upon series of array list           | same in path
    'path' => ['', 'var', 'www', 'html'],              # <-----
    #          ^ this mean absolute path starting with "/"
    # (!) in this form separator is not necessary

    # default separator is "/"
    'separator' => '/'
]);

### Build Existing Object -------------------------------------------------------
$newPath = (new PathJoinUri)->setSeparator('\\');
                                   # ^ you have to set separator before parse()
$parsedUri = $newPath->parse('\\..\\htdocs\\project\\');
         # = ['path' => ['', '..', 'htdocs', 'project']]

$newPath->fromArray($parsedUri);

## print out path
echo $newPath
    ->toString()
    .'<br/>';
# output : \..\htdocs\project

### Append/Prepend Paths --------------------------------------------------------
$joinedPath->append($newPath);

echo $joinedPath->toString().'<br/>';;
# output: /var/www/html/../htdocs/project

# normalize output
echo $joinedPath->normalize()->toString().'<br/>';;
# output: /var/www/htdocs/project

# (!) Paths starting with "../" turning path into relative on normalize
$joinedPath->prepend(new PathJoinUri(['path' => '/../relative']));
echo $joinedPath->toString().'<br/>';
# output: /../relative/var/www/htdocs/project
echo $joinedPath->normalize()->toString().'<br/>';
# output: relative/var/www/htdocs/project
```
