// Preloaded starter news and categories from MySQL database
// Ensures the app is never empty even on first launch or offline
class StarterData {
  static const List<Map<String, dynamic>> categories = [
    {
        "id": 1,
        "slug": "local",
        "name": "Local",
        "name_en": "Local",
        "name_si": "දේශීය",
        "name_ta": "உள்ளூர்",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 1
    },
    {
        "id": 2,
        "slug": "politics",
        "name": "Politics",
        "name_en": "Politics",
        "name_si": "දේශපාලන",
        "name_ta": "அரசியல்",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 2
    },
    {
        "id": 3,
        "slug": "business",
        "name": "Business",
        "name_en": "Business",
        "name_si": "ව්‍යාපාර",
        "name_ta": "வணிகம்",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 3
    },
    {
        "id": 4,
        "slug": "sports",
        "name": "Sports",
        "name_en": "Sports",
        "name_si": "ක්‍රීඩා",
        "name_ta": "விளையாட்டு",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 4
    },
    {
        "id": 5,
        "slug": "entertainment",
        "name": "Entertainment",
        "name_en": "Entertainment",
        "name_si": "විනෝදාස්වාද",
        "name_ta": "பொழுதுபோக்கு",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 5
    },
    {
        "id": 6,
        "slug": "technology",
        "name": "Technology",
        "name_en": "Technology",
        "name_si": "තාක්ෂණය",
        "name_ta": "தொழில்நுட்பம்",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 6
    },
    {
        "id": 7,
        "slug": "international",
        "name": "International",
        "name_en": "International",
        "name_si": "විදෙස්",
        "name_ta": "சர்வதேசம்",
        "color": "#C41230",
        "icon": "newspaper",
        "order": 7
    }
];

  static const List<Map<String, dynamic>> articles = [
    {
        "id": 73,
        "slug": "cabinet-approves-landmark-inland-waterways-transport-project-for-greater-colombo",
        "title": "Cabinet Approves Landmark Inland Waterways Transport Project for Greater Colombo",
        "summary": "The Cabinet of Ministers has granted formal approval for a modern passenger boat transport network connecting Battaramulla, Wellawatte, and Colombo Fort.",
        "titles": {
            "en": "Cabinet Approves Landmark Inland Waterways Transport Project for Greater Colombo",
            "si": "බස්නාහිර පළාතේ නව ජල ප්‍රවාහන ව්‍යාපෘතියට කැබිනට් අනුමැතිය හිමිවෙයි",
            "ta": "மேல் மாகாணத்தில் புதிய நீர்வழிப் போக்குவரத்து திட்டத்திற்கு அமைச்சரவை அனுமதி"
        },
        "summaries": {
            "en": "The Cabinet of Ministers has granted formal approval for a modern passenger boat transport network connecting Battaramulla, Wellawatte, and Colombo Fort.",
            "si": "බත්තරමුල්ල, වැල්ලවත්ත සහ කොළඹ කොටුව සම්බන්ධ කරමින් නවීන මගී ප්‍රවාහන බෝට්ටු සේවාවක් ආරම්භ කිරීමට කැබිනට් මණ්ඩලය අනුමැතිය ලබා දී ඇත.",
            "ta": "பத்தரமுல்ல, வெள்ளவத்தை மற்றும் கொழும்பு கோட்டையை இணைக்கும் நவீன பயணிகள் படகு போக்குவரத்து சேவைக்கு அமைச்சரவை ஒப்புதல் அளித்துள்ளது."
        },
        "contents": {
            "en": "The Cabinet of Ministers has granted formal approval for a modern passenger boat transport network connecting Battaramulla, Wellawatte, and Colombo Fort.",
            "si": "බත්තරමුල්ල, වැල්ලවත්ත සහ කොළඹ කොටුව සම්බන්ධ කරමින් නවීන මගී ප්‍රවාහන බෝට්ටු සේවාවක් ආරම්භ කිරීමට කැබිනට් මණ්ඩලය අනුමැතිය ලබා දී ඇත.",
            "ta": "பத்தரமுல்ல, வெள்ளவத்தை மற்றும் கொழும்பு கோட்டையை இணைக்கும் நவீன பயணிகள் படகு போக்குவரத்து சேவைக்கு அமைச்சரவை ஒப்புதல் அளித்துள்ளது."
        },
        "author_name": "Preview Editor",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/uploads/media/news_colombo_port_city.jpg",
        "video_url": "",
        "is_breaking": true,
        "is_featured": true,
        "views": 8642,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 15:48:57"
    },
    {
        "id": 74,
        "slug": "southern-expressway-upgrades-begin-to-ease-southern-province-tourism-traffic",
        "title": "Southern Expressway Upgrades Begin to Ease Southern Province Tourism Traffic",
        "summary": "The Road Development Authority has commenced expansion works on key interchanges along the Southern Expressway to facilitate growing holiday and cargo transit.",
        "titles": {
            "en": "Southern Expressway Upgrades Begin to Ease Southern Province Tourism Traffic",
            "si": "දකුණු අධිවේගී මාර්ගයේ සංචාරක තදබදය අවම කිරීමට නව සංවර්ධන කටයුතු ඇරඹේ",
            "ta": "தெற்கு அதிவேக நெடுஞ்சாலையில் சுற்றுலாப் போக்குவரத்தைக் குறைக்க புதிய அபிவிருத்திகள் ஆரம்பம்"
        },
        "summaries": {
            "en": "The Road Development Authority has commenced expansion works on key interchanges along the Southern Expressway to facilitate growing holiday and cargo transit.",
            "si": "දකුණු පළාතේ සංචාරක ව්‍යාපාරය දිරිගැන්වීම සඳහා දක්ෂිණ ලංකා අධිවේගී මාර්ගයේ ප්‍රධාන පිවිසුම් පුළුල් කිරීමේ කටයුතු ආරම්භ කර තිබේ.",
            "ta": "தென் மாகாணத்தின் சுற்றுலாத் துறையை மேம்படுத்துவதற்காக தெற்கு அதிவேக நெடுஞ்சாலையின் முக்கிய நுழைவாயில்களை விஸ்தரிக்கும் பணிகள் ஆரம்பிக்கப்பட்டுள்ளன."
        },
        "contents": {
            "en": "The Road Development Authority has commenced expansion works on key interchanges along the Southern Expressway to facilitate growing holiday and cargo transit.",
            "si": "දකුණු පළාතේ සංචාරක ව්‍යාපාරය දිරිගැන්වීම සඳහා දක්ෂිණ ලංකා අධිවේගී මාර්ගයේ ප්‍රධාන පිවිසුම් පුළුල් කිරීමේ කටයුතු ආරම්භ කර තිබේ.",
            "ta": "தென் மாகாணத்தின் சுற்றுலாத் துறையை மேம்படுத்துவதற்காக தெற்கு அதிவேக நெடுஞ்சாலையின் முக்கிய நுழைவாயில்களை விஸ்தரிக்கும் பணிகள் ஆரம்பிக்கப்பட்டுள்ளன."
        },
        "author_name": "Preview Editor",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/uploads/media/news_colombo_port_city.jpg",
        "video_url": "",
        "is_breaking": false,
        "is_featured": true,
        "views": 7333,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 13:32:17"
    },
    {
        "id": 75,
        "slug": "kandy-esala-perahera-preparations-underway-with-enhanced-cultural-heritage-security",
        "title": "Kandy Esala Perahera Preparations Underway with Enhanced Cultural Heritage Security",
        "summary": "The Sri Dalada Maligawa and Central Province administration have finalized comprehensive arrangements for the grand annual cultural pageant.",
        "titles": {
            "en": "Kandy Esala Perahera Preparations Underway with Enhanced Cultural Heritage Security",
            "si": "ඓතිහාසික මහනුවර ඇසළ පෙරහැර මංගල්‍යයේ ආරක්ෂාව සහ සංවිධාන කටයුතු පුළුල් කෙරේ",
            "ta": "வரலாற்று சிறப்புமிக்க கண்டி எசல பெரஹெர பாதுகாப்பும் ஏற்பாடுகளும் பலப்படுத்தப்பட்டன"
        },
        "summaries": {
            "en": "The Sri Dalada Maligawa and Central Province administration have finalized comprehensive arrangements for the grand annual cultural pageant.",
            "si": "ශ්‍රී දළදා මාළිගාවේ වාර්ෂික ඇසළ මහා පෙරහැර මංගල්‍යය වෙනුවෙන් විශේෂ ආරක්ෂක වැඩපිළිවෙළක් සහ පහසුකම් සලසා ඇත.",
            "ta": "ஸ்ரீ தலதா மாளிகையின் வருடாந்த எசல பெரஹெர நிகழ்விற்கான சிறப்பு பாதுகாப்பு ஏற்பாடுகளும் வசதிகளும் பூர்த்தி செய்யப்பட்டுள்ளன."
        },
        "contents": {
            "en": "The Sri Dalada Maligawa and Central Province administration have finalized comprehensive arrangements for the grand annual cultural pageant.",
            "si": "ශ්‍රී දළදා මාළිගාවේ වාර්ෂික ඇසළ මහා පෙරහැර මංගල්‍යය වෙනුවෙන් විශේෂ ආරක්ෂක වැඩපිළිවෙළක් සහ පහසුකම් සලසා ඇත.",
            "ta": "ஸ்ரீ தலதா மாளிகையின் வருடாந்த எசல பெரஹெர நிகழ்விற்கான சிறப்பு பாதுகாப்பு ஏற்பாடுகளும் வசதிகளும் பூர்த்தி செய்யப்பட்டுள்ளன."
        },
        "author_name": "Preview Editor",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/uploads/media/news_colombo_port_city.jpg",
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 7342,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 10:12:30"
    },
    {
        "id": 76,
        "slug": "central-highlands-tea-estates-record-surge-in-high-grown-orthodox-crop-yields",
        "title": "Central Highlands Tea Estates Record Surge in High-Grown Orthodox Crop Yields",
        "summary": "Favorable weather across Nuwara Eliya and Hatton has propelled high-grown orthodox tea output to its strongest quarterly performance in five years.",
        "titles": {
            "en": "Central Highlands Tea Estates Record Surge in High-Grown Orthodox Crop Yields",
            "si": "මධ්‍යම කඳුකරයේ උඩරට තේ අස්වැන්න කැපී පෙනෙන ලෙස ඉහළ යයි",
            "ta": "மத்திய மலைநாட்டு உயர் ரக தேயிலை உற்பத்தி கணிசமாக அதிகரிப்பு"
        },
        "summaries": {
            "en": "Favorable weather across Nuwara Eliya and Hatton has propelled high-grown orthodox tea output to its strongest quarterly performance in five years.",
            "si": "නුවරඑළිය සහ හැටන් ප්‍රදේශවල පවතින හිතකර කාලගුණය හේතුවෙන් මෙවර උඩරට තේ අස්වැන්න ඉහළ ගොස් ඇත.",
            "ta": "நுவரெலியா மற்றும் ஹட்டன் பகுதிகளில் நிலவும் சாதகமான வானிலை காரணமாக உயர் ரக தேயிலை உற்பத்தி அதிகரித்துள்ளது."
        },
        "contents": {
            "en": "Favorable weather across Nuwara Eliya and Hatton has propelled high-grown orthodox tea output to its strongest quarterly performance in five years.",
            "si": "නුවරඑළිය සහ හැටන් ප්‍රදේශවල පවතින හිතකර කාලගුණය හේතුවෙන් මෙවර උඩරට තේ අස්වැන්න ඉහළ ගොස් ඇත.",
            "ta": "நுவரெலியா மற்றும் ஹட்டன் பகுதிகளில் நிலவும் சாதகமான வானிலை காரணமாக உயர் ரக தேயிலை உற்பத்தி அதிகரித்துள்ளது."
        },
        "author_name": "Preview Editor",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/uploads/media/news_colombo_port_city.jpg",
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 1543,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 07:35:47"
    },
    {
        "id": 71,
        "slug": "story-20261007-5cfa51",
        "title": "",
        "summary": "",
        "titles": {
            "en": "",
            "si": "ශ්‍රී ලංකා මහ බැංකුව පොලී අනුපාත සංශෝධනය කරයි",
            "ta": ""
        },
        "summaries": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "contents": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "author_name": "Amal Perera (Sinhala Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:51:50"
    },
    {
        "id": 72,
        "slug": "story-20261007-ee1e28",
        "title": "",
        "summary": "",
        "titles": {
            "en": "",
            "si": "",
            "ta": "இலங்கை மத்திய வங்கி வட்டி விகிதங்களை திருத்தியமைத்துள்ளது"
        },
        "summaries": {
            "en": "",
            "si": "",
            "ta": "மத்திய வங்கி அறிவிப்பு"
        },
        "contents": {
            "en": "",
            "si": "",
            "ta": "மத்திய வங்கி அறிவிப்பு"
        },
        "author_name": "Selvam Kumar (Tamil Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:51:50"
    },
    {
        "id": 69,
        "slug": "story-20261007-451bf0",
        "title": "",
        "summary": "",
        "titles": {
            "en": "",
            "si": "ශ්‍රී ලංකා මහ බැංකුව පොලී අනුපාත සංශෝධනය කරයි",
            "ta": ""
        },
        "summaries": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "contents": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "author_name": "Amal Perera (Sinhala Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:51:35"
    },
    {
        "id": 70,
        "slug": "story-20261007-ba201b",
        "title": "",
        "summary": "",
        "titles": {
            "en": "",
            "si": "",
            "ta": "இலங்கை மத்திய வங்கி வட்டி விகிதங்களை திருத்தியமைத்துள்ளது"
        },
        "summaries": {
            "en": "",
            "si": "",
            "ta": "மத்திய வங்கி அறிவிப்பு"
        },
        "contents": {
            "en": "",
            "si": "",
            "ta": "மத்திய வங்கி அறிவிப்பு"
        },
        "author_name": "Selvam Kumar (Tamil Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:51:35"
    },
    {
        "id": 67,
        "slug": "story-20261007-1919e6",
        "title": "",
        "summary": "",
        "titles": {
            "en": "",
            "si": "ශ්‍රී ලංකා මහ බැංකුව පොලී අනුපාත සංශෝධනය කරයි",
            "ta": ""
        },
        "summaries": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "contents": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "author_name": "Amal Perera (Sinhala Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:51:15"
    },
    {
        "id": 68,
        "slug": "story-20261007-d771f8",
        "title": "",
        "summary": "",
        "titles": {
            "en": "",
            "si": "",
            "ta": "இலங்கை மத்திய வங்கி வட்டி விகிதங்களை திருத்தியமைத்துள்ளது"
        },
        "summaries": {
            "en": "",
            "si": "",
            "ta": "மத்திய வங்கி அறிவிப்பு"
        },
        "contents": {
            "en": "",
            "si": "",
            "ta": "மத்திய வங்கி அறிவிப்பு"
        },
        "author_name": "Selvam Kumar (Tamil Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:51:15"
    },
    {
        "id": 66,
        "slug": "story-20261007-ed41e2",
        "title": null,
        "summary": null,
        "titles": {
            "en": "",
            "si": "ශ්‍රී ලංකා මහ බැංකුව පොලී අනුපාත සංශෝධනය කරයි",
            "ta": ""
        },
        "summaries": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "contents": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "author_name": "Amal Perera (Sinhala Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:48:54"
    },
    {
        "id": 65,
        "slug": "story-20261007-e41297",
        "title": null,
        "summary": null,
        "titles": {
            "en": "",
            "si": "ශ්‍රී ලංකා මහ බැංකුව පොලී අනුපාත සංශෝධනය කරයි",
            "ta": ""
        },
        "summaries": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "contents": {
            "en": "",
            "si": "මහ බැංකු නිවේදනය",
            "ta": ""
        },
        "author_name": "Amal Perera (Sinhala Desk)",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": null,
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 06:48:32"
    },
    {
        "id": 77,
        "slug": "mannar-island-coastal-biodiversity-zone-declared-protected-marine-sanctuary",
        "title": "Mannar Island Coastal Biodiversity Zone Declared Protected Marine Sanctuary",
        "summary": "The Wildlife Department has gazetted over 15,000 hectares of critical migratory bird habitat and pristine coral reefs as an official national reserve.",
        "titles": {
            "en": "Mannar Island Coastal Biodiversity Zone Declared Protected Marine Sanctuary",
            "si": "මන්නාරම වෙරළබඩ ජෛව කලාපය ආරක්ෂිත සාගර අභයභූමියක් ලෙස ප්‍රකාශයට පත්කෙරේ",
            "ta": "மன்னார் கடலோர பல்லுயிர் வலயமானது பாதுகாக்கப்பட்ட கடல் சரணாலயமாக பிரகடனம்"
        },
        "summaries": {
            "en": "The Wildlife Department has gazetted over 15,000 hectares of critical migratory bird habitat and pristine coral reefs as an official national reserve.",
            "si": "සංක්‍රමණික පක්ෂීන් සහ කොරල් පර ආරක්ෂා කිරීම වෙනුවෙන් මන්නාරම් බොක්ක ආශ්‍රිත ප්‍රදේශය අභයභූමියක් බවට පත් කර ඇත.",
            "ta": "வலசைபோகும் பறவைகள் மற்றும் பவளப்பாறைகளை பாதுகாக்கும் நோக்கில் மன்னார் வளைகுடா பகுதி சரணாலயமாக அறிவிக்கப்பட்டுள்ளது."
        },
        "contents": {
            "en": "The Wildlife Department has gazetted over 15,000 hectares of critical migratory bird habitat and pristine coral reefs as an official national reserve.",
            "si": "සංක්‍රමණික පක්ෂීන් සහ කොරල් පර ආරක්ෂා කිරීම වෙනුවෙන් මන්නාරම් බොක්ක ආශ්‍රිත ප්‍රදේශය අභයභූමියක් බවට පත් කර ඇත.",
            "ta": "வலசைபோகும் பறவைகள் மற்றும் பவளப்பாறைகளை பாதுகாக்கும் நோக்கில் மன்னார் வளைகுடா பகுதி சரணாலயமாக அறிவிக்கப்பட்டுள்ளது."
        },
        "author_name": "Preview Editor",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/uploads/media/news_colombo_skyline.png",
        "video_url": "",
        "is_breaking": false,
        "is_featured": true,
        "views": 1189,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 04:31:14"
    },
    {
        "id": 78,
        "slug": "jaffna-international-airport-expands-daily-regional-commercial-flight-operations",
        "title": "Jaffna International Airport Expands Daily Regional Commercial Flight Operations",
        "summary": "Palaly Airport will handle double the scheduled flights connecting northern Sri Lanka directly with Chennai and regional hubs from next week.",
        "titles": {
            "en": "Jaffna International Airport Expands Daily Regional Commercial Flight Operations",
            "si": "යාපනය ජාත්‍යන්තර ගුවන්තොටුපළේ දෛනික ගුවන් ගමන් වාර සංඛ්‍යාව ඉහළ දමයි",
            "ta": "யாழ்ப்பாணம் சர்வதேச விமான நிலையத்தின் தினசரி விமான சேவைகள் விஸ்தரிப்பு"
        },
        "summaries": {
            "en": "Palaly Airport will handle double the scheduled flights connecting northern Sri Lanka directly with Chennai and regional hubs from next week.",
            "si": "පලාලි ගුවන්තොටුපළ සහ ඉන්දියාවේ චෙන්නායි අතර ගුවන් සේවා තවදුරටත් පුළුල් කිරීමට පියවර ගෙන තිබේ.",
            "ta": "பலாலி விமான நிலையத்திற்கும் சென்னைக்கும் இடையிலான தினசரி விமான சேவைகள் அடுத்த வாரம் முதல் இரட்டிப்பாக்கப்படவுள்ளன."
        },
        "contents": {
            "en": "Palaly Airport will handle double the scheduled flights connecting northern Sri Lanka directly with Chennai and regional hubs from next week.",
            "si": "පලාලි ගුවන්තොටුපළ සහ ඉන්දියාවේ චෙන්නායි අතර ගුවන් සේවා තවදුරටත් පුළුල් කිරීමට පියවර ගෙන තිබේ.",
            "ta": "பலாலி விமான நிலையத்திற்கும் சென்னைக்கும் இடையிலான தினசரி விமான சேவைகள் அடுத்த வாரம் முதல் இரட்டிப்பாக்கப்படவுள்ளன."
        },
        "author_name": "Preview Editor",
        "category_id": 1,
        "category_name": "Local",
        "category_slug": "local",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/uploads/media/news_tea_harvest.png",
        "video_url": "",
        "is_breaking": true,
        "is_featured": false,
        "views": 3948,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 02:19:54"
    },
    {
        "id": 64,
        "slug": "asian-relay-grand-prix-sri-lanka-wins-double-gold",
        "title": "Sri Lanka Sprint Quartet Grabs Double Gold at Asian Relay Championships",
        "summary": "The men’s and women’s 4x400m relay teams shattered national records on a triumphant evening in Bangkok, qualifying for world championships.",
        "titles": {
            "en": "Sri Lanka Sprint Quartet Grabs Double Gold at Asian Relay Championships",
            "si": "ආසියානු රිලේ ශූරතාවලියේදී ශ්‍රී ලංකා කෙටිදුර ධාවකයන් රන් පදක්කම් ද්විත්වයක් දිනාගනී",
            "ta": "ஆசிய ரிலே சாம்பியன்ஷிப்பில் இலங்கை தடகள அணி இரண்டு தங்கப் பதக்கங்களை வென்றது"
        },
        "summaries": {
            "en": "The men’s and women’s 4x400m relay teams shattered national records on a triumphant evening in Bangkok, qualifying for world championships.",
            "si": "",
            "ta": ""
        },
        "contents": {
            "en": "The men’s and women’s 4x400m relay teams shattered national records on a triumphant evening in Bangkok, qualifying for world championships.",
            "si": "",
            "ta": ""
        },
        "author_name": "Preview Editor",
        "category_id": 4,
        "category_name": "Sports",
        "category_slug": "sports",
        "category_color": "#C41230",
        "image_url": "http://172.20.10.5:8080/assets/images/news/sports_medvedev.jpg",
        "video_url": "",
        "is_breaking": false,
        "is_featured": false,
        "views": 0,
        "tags": [
            "News",
            "Sri Lanka"
        ],
        "published_at": "2026-10-07 01:05:37"
    }
];
}