USE csist_new;

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', "CSI St. Matthew's Church"),
('site_location', 'Porur, Chennai'),
('tagline', "Engaging God's world through faith"),
('address', '8/28, CSI Church St., Jayanagar, Porur, Chennai'),
('email', 'csistmatthewschurchporur@gmail.com'),
('youtube_channel', 'https://www.youtube.com/channel/UCExRpRYYyuJdRfER1-UNuog'),
('service_card_enabled', '1'),
('service_date', '27th September 2026'),
('service_morning', '8:30 AM'),
('service_evening', '6:30 PM'),
('history', "Church of South India Trust Association (CSI TA) was formed in 1947. Under CSI TA there are 24 Dioceses situated in South India and Sri Lanka, each consisting of 500 to 1000 churches, nearly 4 million people in total.\n\nOur church, CSI St. Matthew's Church, is located in Porur, Chennai, Tamil Nadu, India, and is a unit of the Madras Diocese, which is affiliated to the Church of South India Trust Association (CSITA).\n\nCSI St. Matthew's Church, Porur was started with very few families as members in the 1990s. It grew steadily, and by 2020 over 700 families had joined as members. The church now gathers its believers every Sunday for worship across three different services at different timings, from morning to evening.\n\nApart from worship, several other events and meetings are conducted throughout the year. The whole purpose of this is to spread the gospel to all and be ready for His second coming.");

INSERT INTO leaders (role, name, contact, sort_order) VALUES
('Presbyter & Chairman', 'Rev. C. Samuel Jebakumar, MSW., Mth', '9080303965', 1),
('Secretary', 'Mr. Albert Kings Bell', '9941545732', 2),
('Treasurer', 'Mr. P. Moses Daniel Raj', '9444223631', 3);

INSERT INTO fellowships (slug, title, tagline, description, gallery_slug, sort_order) VALUES
('mens-fellowship', "Men's Fellowship", 'We keep kneeling to seek the beauty of the Lord', 'The Men''s Fellowship brings together the men of our congregation for prayer, Bible study, and fellowship, strengthening their walk of faith together.', 'mens-fellowship', 1),
('womens-fellowship', "Women's Fellowship", 'Part of the body of Christ', 'The Women''s Fellowship gathers regularly for prayer meetings and an annual retreat, building a supportive community of faith among the women of the church.', 'womens-fellowship', 2),
('youth', 'Youth', 'Remembering the Creator in the days of our youth', 'Our Youth Fellowship engages young members through events like the annual Lenten Night, encouraging them to grow in faith and fellowship.', 'youth', 3),
('sunday-school', 'Sunday School', 'Train up a child in the way he should go', 'Sunday School nurtures our children in the Christian faith through weekly classes, an annual Christmas programme, and a Sunday School retreat.', 'sunday-school', 4),
('choir', 'Choir', "With a joyful heart I'll sing to the Lord", 'Our Choir leads the congregation in worship through music, ministering through song at Sunday services and special occasions.', 'choir', 5),
('missionary-work', 'Missionary Work', "Expression of the Lord's redeeming love", 'The church actively supports missionary work, extending the gospel and practical support beyond our own congregation.', 'missionary-work', 6);

INSERT INTO gallery_categories (slug, title, folder, sort_order) VALUES
('church-history', 'Church History', 'church-history', 1),
('church-event', 'Church Events', 'church-event', 2),
('youth', 'Youth', 'youth', 3),
('sunday-school', 'Sunday School', 'sunday-school', 4),
('missionary-work', 'Missionary Work', 'missionary-work', 5),
('womens-fellowship', "Women's Fellowship", 'womens-fellowship', 6),
('mens-fellowship', "Men's Fellowship", 'mens-fellowship', 7),
('choir', 'Choir', 'choir', 8);

INSERT INTO gallery_images (category_id, filename, alt) VALUES
((SELECT id FROM gallery_categories WHERE slug='church-history'), 'Untitled-design-2020-05-01T201522.258-1024x614.jpg', 'Church history'),
((SELECT id FROM gallery_categories WHERE slug='missionary-work'), '1-3.jpg', 'Missionary work'),
((SELECT id FROM gallery_categories WHERE slug='choir'), '3-4.jpg', 'Choir'),
((SELECT id FROM gallery_categories WHERE slug='choir'), '1-2-1024x998.jpg', 'Choir'),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '4-768x1024.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '7-1024x768.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '9-768x1024.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '11.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '14.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '222.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='mens-fellowship'), '444.jpg', "Men's Fellowship"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '20-2.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '18-2-1024x579.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '15-1.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '11-2-1024x465.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '4-3.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '3-2.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '2-2.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '26-2.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='womens-fellowship'), '23-1.jpg', "Women's Fellowship retreat"),
((SELECT id FROM gallery_categories WHERE slug='youth'), '6-768x1024.jpg', 'Lenten Night'),
((SELECT id FROM gallery_categories WHERE slug='youth'), '5-1024x768.jpg', 'Lenten Night'),
((SELECT id FROM gallery_categories WHERE slug='youth'), '8-1024x768.jpg', 'Lenten Night'),
((SELECT id FROM gallery_categories WHERE slug='youth'), '7-1024x768.jpg', 'Lenten Night'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '1-1.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '2-1-1024x576.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '3-1.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '4-2.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '5-2-1024x576.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '6-2-1024x576.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '11-1-1024x576.jpg', 'Sunday School'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '29-1-1024x768.jpg', 'Sunday School retreat'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '18-1.jpg', 'Christmas'),
((SELECT id FROM gallery_categories WHERE slug='sunday-school'), '19-1.jpg', 'Christmas'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '26-1024x576.jpg', 'Camp fire'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '22.jpg', 'Camp fire'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '23.jpg', 'Camp fire'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '24.jpg', 'Camp fire'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '25.jpg', 'Camp fire'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '17-576x1024.jpg', 'Candle Light Carol'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '19-1024x576.jpg', 'Candle Light Carol'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '1.jpg', 'Church event'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '2.jpg', 'Church event'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '3.jpg', 'Church event'),
((SELECT id FROM gallery_categories WHERE slug='church-event'), '333.jpg', 'Harvest Festival');

INSERT INTO magazines (title, period, filename, sort_order) VALUES
('Monthly Magazine', 'September 2026', 'September-2026.pdf', 0),
('Monthly Magazine', 'June 2021', 'June-2021.pdf', 1),
('Monthly Magazine', 'August 2020', 'August-2020.pdf', 2),
('Monthly Magazine', 'July 2020', 'July2020.pdf', 3),
('Monthly Magazine', 'May 2020', 'May-2020-Magazine-1.pdf', 4);

INSERT INTO celebrations (type, name, occasion_date, is_active, sort_order) VALUES
('birthday', 'Add a name in Admin', '1 January', 0, 1),
('anniversary', 'Add a name in Admin', '1 January', 0, 1);
