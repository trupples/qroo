
insert into Folders(id, name, parent, description, creation_date) values (10, 'xss test parent<script>alert(1)</script>', 0, '<script>alert(2)</script>', '<b>123</b>');
insert into Folders(id, name, parent, description, creation_date) values (11, 'xss test child<script>alert(3)</script>', 10, '<script>alert(4)</script>', '<b>123</b>');
insert into Media(name, description) values ('<script>alert(6)</script>', '<script>alert(5)</script>');
insert into Codes(code, folder, medium, redirect_url, creation_date) values ('<b>!</b>', 10, '<script>alert(7)</script>', '<script>alert(9)</script>', '<b>123</b>');
insert into Codes(code, folder, medium, redirect_url, creation_date) values ('<b>?</b>', 11, '<script>alert(8)</script>', '<script>alert(0)</script>', '<b>123</b>');
