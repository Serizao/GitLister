
# GitLister

 ![made-with-PHP](https://img.shields.io/badge/Made%20with-PHP-1f425f.svg)  ![MIT license](https://img.shields.io/badge/License-MIT-blue.svg)


__This tools work with github only (for the moment)__

This tool allows you to exit wordlists from repo git without downloading the git in question. This can be used with tools in dirsearch to make a discovery on a website

## Usage

```
php gitlister.php --repo=http://github.com/repo
```
 
 you can use it for enumerate root of a git :
 
 ```
 php gitlister.php --repo=https://github.com/Serizao/kolab_tags
 ```
 
 or for enumerate secificate folder in this git
 
 ```
 php gitlister.php --repo=https://github.com/Serizao/kolab_tags/tree/master/kolab_tags
 ```
