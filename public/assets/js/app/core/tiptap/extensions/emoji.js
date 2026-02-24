/**
 * Emoji Extension for Tiptap
 *
 * Enables inserting emojis using :shortcode: syntax (Slack/Discord style).
 * Shows a searchable popup when typing : followed by characters.
 *
 * @module tiptap/extensions/emoji
 */

import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';

// Common emojis with shortcodes
var emojiData = [
    // Smileys & Emotion
    { shortcode: 'smile', emoji: '\u{1F604}', keywords: ['happy', 'joy', 'grin'] },
    { shortcode: 'grin', emoji: '\u{1F600}', keywords: ['happy', 'smile'] },
    { shortcode: 'joy', emoji: '\u{1F602}', keywords: ['laugh', 'tears', 'happy', 'lol'] },
    { shortcode: 'rofl', emoji: '\u{1F923}', keywords: ['laugh', 'rolling'] },
    { shortcode: 'wink', emoji: '\u{1F609}', keywords: ['flirt', 'playful'] },
    { shortcode: 'blush', emoji: '\u{1F60A}', keywords: ['happy', 'shy', 'smile'] },
    { shortcode: 'innocent', emoji: '\u{1F607}', keywords: ['angel', 'halo'] },
    { shortcode: 'heart_eyes', emoji: '\u{1F60D}', keywords: ['love', 'crush', 'adore'] },
    { shortcode: 'kissing_heart', emoji: '\u{1F618}', keywords: ['love', 'kiss'] },
    { shortcode: 'thinking', emoji: '\u{1F914}', keywords: ['hmm', 'consider', 'wonder'] },
    { shortcode: 'raised_eyebrow', emoji: '\u{1F928}', keywords: ['skeptical', 'disbelief'] },
    { shortcode: 'neutral', emoji: '\u{1F610}', keywords: ['meh', 'indifferent'] },
    { shortcode: 'expressionless', emoji: '\u{1F611}', keywords: ['blank', 'meh'] },
    { shortcode: 'rolling_eyes', emoji: '\u{1F644}', keywords: ['whatever', 'bored'] },
    { shortcode: 'smirk', emoji: '\u{1F60F}', keywords: ['smug', 'sly'] },
    { shortcode: 'persevere', emoji: '\u{1F623}', keywords: ['struggle', 'endure'] },
    { shortcode: 'disappointed', emoji: '\u{1F61E}', keywords: ['sad', 'upset'] },
    { shortcode: 'worried', emoji: '\u{1F61F}', keywords: ['anxious', 'nervous'] },
    { shortcode: 'confused', emoji: '\u{1F615}', keywords: ['puzzled'] },
    { shortcode: 'slight_frown', emoji: '\u{1F641}', keywords: ['sad'] },
    { shortcode: 'frown', emoji: '\u{2639}\u{FE0F}', keywords: ['sad', 'unhappy'] },
    { shortcode: 'open_mouth', emoji: '\u{1F62E}', keywords: ['surprise', 'wow'] },
    { shortcode: 'hushed', emoji: '\u{1F62F}', keywords: ['surprise', 'shock'] },
    { shortcode: 'astonished', emoji: '\u{1F632}', keywords: ['shock', 'surprise'] },
    { shortcode: 'flushed', emoji: '\u{1F633}', keywords: ['embarrassed', 'blush'] },
    { shortcode: 'fearful', emoji: '\u{1F628}', keywords: ['scared', 'afraid'] },
    { shortcode: 'cold_sweat', emoji: '\u{1F630}', keywords: ['nervous', 'anxious'] },
    { shortcode: 'cry', emoji: '\u{1F622}', keywords: ['sad', 'tears'] },
    { shortcode: 'sob', emoji: '\u{1F62D}', keywords: ['cry', 'sad', 'tears'] },
    { shortcode: 'scream', emoji: '\u{1F631}', keywords: ['horror', 'shock'] },
    { shortcode: 'angry', emoji: '\u{1F620}', keywords: ['mad', 'grumpy'] },
    { shortcode: 'rage', emoji: '\u{1F621}', keywords: ['angry', 'mad', 'furious'] },
    { shortcode: 'triumph', emoji: '\u{1F624}', keywords: ['winning', 'proud'] },
    { shortcode: 'sleepy', emoji: '\u{1F62A}', keywords: ['tired', 'drowsy'] },
    { shortcode: 'yawning', emoji: '\u{1F971}', keywords: ['tired', 'bored', 'sleepy'] },
    { shortcode: 'mask', emoji: '\u{1F637}', keywords: ['sick', 'ill'] },
    { shortcode: 'sunglasses', emoji: '\u{1F60E}', keywords: ['cool', 'awesome'] },
    { shortcode: 'nerd', emoji: '\u{1F913}', keywords: ['geek', 'smart'] },
    { shortcode: 'clown', emoji: '\u{1F921}', keywords: ['silly', 'joker'] },
    { shortcode: 'cowboy', emoji: '\u{1F920}', keywords: ['western', 'hat'] },
    { shortcode: 'partying', emoji: '\u{1F973}', keywords: ['party', 'celebrate'] },
    { shortcode: 'shushing', emoji: '\u{1F92B}', keywords: ['quiet', 'secret'] },
    { shortcode: 'zany', emoji: '\u{1F92A}', keywords: ['crazy', 'wild', 'silly'] },
    { shortcode: 'monocle', emoji: '\u{1F9D0}', keywords: ['fancy', 'curious'] },
    { shortcode: 'skull', emoji: '\u{1F480}', keywords: ['dead', 'death', 'skeleton'] },
    { shortcode: 'ghost', emoji: '\u{1F47B}', keywords: ['spooky', 'halloween'] },
    { shortcode: 'alien', emoji: '\u{1F47D}', keywords: ['ufo', 'space'] },
    { shortcode: 'robot', emoji: '\u{1F916}', keywords: ['bot', 'machine'] },
    { shortcode: 'poop', emoji: '\u{1F4A9}', keywords: ['crap', 'poo'] },

    // Gestures & People
    { shortcode: 'wave', emoji: '\u{1F44B}', keywords: ['hello', 'hi', 'goodbye', 'bye'] },
    { shortcode: 'ok_hand', emoji: '\u{1F44C}', keywords: ['perfect', 'nice'] },
    { shortcode: 'pinched', emoji: '\u{1F90C}', keywords: ['small', 'tiny'] },
    { shortcode: 'v', emoji: '\u{270C}\u{FE0F}', keywords: ['peace', 'victory', 'two'] },
    { shortcode: 'crossed_fingers', emoji: '\u{1F91E}', keywords: ['luck', 'hope'] },
    { shortcode: 'call_me', emoji: '\u{1F919}', keywords: ['phone', 'shaka'] },
    { shortcode: 'point_left', emoji: '\u{1F448}', keywords: ['left', 'direction'] },
    { shortcode: 'point_right', emoji: '\u{1F449}', keywords: ['right', 'direction'] },
    { shortcode: 'point_up', emoji: '\u{1F446}', keywords: ['up', 'direction'] },
    { shortcode: 'point_down', emoji: '\u{1F447}', keywords: ['down', 'direction'] },
    { shortcode: 'thumbsup', emoji: '\u{1F44D}', keywords: ['yes', 'good', 'like', '+1'] },
    { shortcode: 'thumbsdown', emoji: '\u{1F44E}', keywords: ['no', 'bad', 'dislike', '-1'] },
    { shortcode: 'fist', emoji: '\u{270A}', keywords: ['power', 'punch'] },
    { shortcode: 'fist_bump', emoji: '\u{1F91C}', keywords: ['punch', 'bro'] },
    { shortcode: 'clap', emoji: '\u{1F44F}', keywords: ['applause', 'congrats'] },
    { shortcode: 'raised_hands', emoji: '\u{1F64C}', keywords: ['celebrate', 'praise', 'hooray'] },
    { shortcode: 'pray', emoji: '\u{1F64F}', keywords: ['please', 'hope', 'thanks', 'namaste'] },
    { shortcode: 'handshake', emoji: '\u{1F91D}', keywords: ['deal', 'agreement'] },
    { shortcode: 'muscle', emoji: '\u{1F4AA}', keywords: ['strong', 'flex', 'bicep'] },
    { shortcode: 'eyes', emoji: '\u{1F440}', keywords: ['look', 'see', 'watch'] },
    { shortcode: 'brain', emoji: '\u{1F9E0}', keywords: ['smart', 'think', 'mind'] },

    // Hearts & Symbols
    { shortcode: 'heart', emoji: '\u{2764}\u{FE0F}', keywords: ['love', 'red'] },
    { shortcode: 'orange_heart', emoji: '\u{1F9E1}', keywords: ['love'] },
    { shortcode: 'yellow_heart', emoji: '\u{1F49B}', keywords: ['love'] },
    { shortcode: 'green_heart', emoji: '\u{1F49A}', keywords: ['love'] },
    { shortcode: 'blue_heart', emoji: '\u{1F499}', keywords: ['love'] },
    { shortcode: 'purple_heart', emoji: '\u{1F49C}', keywords: ['love'] },
    { shortcode: 'black_heart', emoji: '\u{1F5A4}', keywords: ['love', 'dark'] },
    { shortcode: 'white_heart', emoji: '\u{1F90D}', keywords: ['love', 'pure'] },
    { shortcode: 'broken_heart', emoji: '\u{1F494}', keywords: ['sad', 'heartbreak'] },
    { shortcode: 'sparkling_heart', emoji: '\u{1F496}', keywords: ['love', 'shiny'] },
    { shortcode: 'fire', emoji: '\u{1F525}', keywords: ['hot', 'lit', 'flame'] },
    { shortcode: 'star', emoji: '\u{2B50}', keywords: ['favorite', 'gold'] },
    { shortcode: 'sparkles', emoji: '\u{2728}', keywords: ['shiny', 'magic', 'new'] },
    { shortcode: 'zap', emoji: '\u{26A1}', keywords: ['lightning', 'electric', 'fast'] },
    { shortcode: 'boom', emoji: '\u{1F4A5}', keywords: ['explosion', 'collision'] },
    { shortcode: 'dizzy', emoji: '\u{1F4AB}', keywords: ['stars', 'confused'] },
    { shortcode: '100', emoji: '\u{1F4AF}', keywords: ['perfect', 'score', 'hundred'] },
    { shortcode: 'exclamation', emoji: '\u{2757}', keywords: ['alert', 'important'] },
    { shortcode: 'question', emoji: '\u{2753}', keywords: ['what', 'confused'] },
    { shortcode: 'checkmark', emoji: '\u{2705}', keywords: ['done', 'complete', 'yes'] },
    { shortcode: 'x', emoji: '\u{274C}', keywords: ['no', 'wrong', 'delete'] },

    // Animals & Nature
    { shortcode: 'dog', emoji: '\u{1F436}', keywords: ['puppy', 'pet'] },
    { shortcode: 'cat', emoji: '\u{1F431}', keywords: ['kitten', 'pet'] },
    { shortcode: 'mouse', emoji: '\u{1F42D}', keywords: ['rodent'] },
    { shortcode: 'rabbit', emoji: '\u{1F430}', keywords: ['bunny'] },
    { shortcode: 'fox', emoji: '\u{1F98A}', keywords: ['animal'] },
    { shortcode: 'bear', emoji: '\u{1F43B}', keywords: ['animal'] },
    { shortcode: 'panda', emoji: '\u{1F43C}', keywords: ['animal', 'bear'] },
    { shortcode: 'koala', emoji: '\u{1F428}', keywords: ['animal'] },
    { shortcode: 'tiger', emoji: '\u{1F42F}', keywords: ['animal', 'cat'] },
    { shortcode: 'lion', emoji: '\u{1F981}', keywords: ['animal', 'cat', 'king'] },
    { shortcode: 'unicorn', emoji: '\u{1F984}', keywords: ['magic', 'horse'] },
    { shortcode: 'bee', emoji: '\u{1F41D}', keywords: ['insect', 'buzz'] },
    { shortcode: 'butterfly', emoji: '\u{1F98B}', keywords: ['insect', 'pretty'] },
    { shortcode: 'turtle', emoji: '\u{1F422}', keywords: ['slow', 'animal'] },
    { shortcode: 'octopus', emoji: '\u{1F419}', keywords: ['sea', 'tentacles'] },
    { shortcode: 'crab', emoji: '\u{1F980}', keywords: ['sea', 'pinch'] },
    { shortcode: 'shark', emoji: '\u{1F988}', keywords: ['sea', 'fish'] },
    { shortcode: 'whale', emoji: '\u{1F433}', keywords: ['sea', 'big'] },
    { shortcode: 'dolphin', emoji: '\u{1F42C}', keywords: ['sea', 'smart'] },
    { shortcode: 'bird', emoji: '\u{1F426}', keywords: ['fly', 'tweet'] },
    { shortcode: 'eagle', emoji: '\u{1F985}', keywords: ['bird', 'america'] },
    { shortcode: 'owl', emoji: '\u{1F989}', keywords: ['bird', 'wise', 'night'] },
    { shortcode: 'snake', emoji: '\u{1F40D}', keywords: ['reptile'] },
    { shortcode: 'dragon', emoji: '\u{1F409}', keywords: ['mythical', 'fire'] },
    { shortcode: 'sauropod', emoji: '\u{1F995}', keywords: ['dinosaur', 'dino'] },
    { shortcode: 'trex', emoji: '\u{1F996}', keywords: ['dinosaur', 'dino'] },
    { shortcode: 'tree', emoji: '\u{1F333}', keywords: ['nature', 'plant'] },
    { shortcode: 'evergreen', emoji: '\u{1F332}', keywords: ['tree', 'pine', 'christmas'] },
    { shortcode: 'palm_tree', emoji: '\u{1F334}', keywords: ['tree', 'beach', 'tropical'] },
    { shortcode: 'cactus', emoji: '\u{1F335}', keywords: ['plant', 'desert'] },
    { shortcode: 'flower', emoji: '\u{1F33C}', keywords: ['blossom', 'nature'] },
    { shortcode: 'rose', emoji: '\u{1F339}', keywords: ['flower', 'love', 'red'] },
    { shortcode: 'sunflower', emoji: '\u{1F33B}', keywords: ['flower', 'yellow'] },
    { shortcode: 'four_leaf_clover', emoji: '\u{1F340}', keywords: ['luck', 'irish'] },
    { shortcode: 'mushroom', emoji: '\u{1F344}', keywords: ['fungus', 'nature'] },
    { shortcode: 'sun', emoji: '\u{2600}\u{FE0F}', keywords: ['sunny', 'weather', 'hot'] },
    { shortcode: 'moon', emoji: '\u{1F319}', keywords: ['night', 'sleep'] },
    { shortcode: 'full_moon', emoji: '\u{1F315}', keywords: ['night'] },
    { shortcode: 'cloud', emoji: '\u{2601}\u{FE0F}', keywords: ['weather', 'cloudy'] },
    { shortcode: 'rain', emoji: '\u{1F327}\u{FE0F}', keywords: ['weather', 'wet'] },
    { shortcode: 'snow', emoji: '\u{2744}\u{FE0F}', keywords: ['cold', 'winter', 'snowflake'] },
    { shortcode: 'rainbow', emoji: '\u{1F308}', keywords: ['colors', 'gay', 'pride'] },

    // Food & Drink
    { shortcode: 'apple', emoji: '\u{1F34E}', keywords: ['fruit', 'red'] },
    { shortcode: 'orange', emoji: '\u{1F34A}', keywords: ['fruit', 'tangerine'] },
    { shortcode: 'lemon', emoji: '\u{1F34B}', keywords: ['fruit', 'sour', 'yellow'] },
    { shortcode: 'banana', emoji: '\u{1F34C}', keywords: ['fruit', 'yellow'] },
    { shortcode: 'watermelon', emoji: '\u{1F349}', keywords: ['fruit', 'summer'] },
    { shortcode: 'grapes', emoji: '\u{1F347}', keywords: ['fruit', 'wine'] },
    { shortcode: 'strawberry', emoji: '\u{1F353}', keywords: ['fruit', 'berry', 'red'] },
    { shortcode: 'peach', emoji: '\u{1F351}', keywords: ['fruit'] },
    { shortcode: 'cherry', emoji: '\u{1F352}', keywords: ['fruit', 'red'] },
    { shortcode: 'avocado', emoji: '\u{1F951}', keywords: ['vegetable', 'guac'] },
    { shortcode: 'carrot', emoji: '\u{1F955}', keywords: ['vegetable', 'orange'] },
    { shortcode: 'corn', emoji: '\u{1F33D}', keywords: ['vegetable', 'yellow'] },
    { shortcode: 'hot_pepper', emoji: '\u{1F336}\u{FE0F}', keywords: ['spicy', 'chili'] },
    { shortcode: 'pizza', emoji: '\u{1F355}', keywords: ['food', 'italian'] },
    { shortcode: 'hamburger', emoji: '\u{1F354}', keywords: ['food', 'burger', 'fast food'] },
    { shortcode: 'fries', emoji: '\u{1F35F}', keywords: ['food', 'fast food'] },
    { shortcode: 'hotdog', emoji: '\u{1F32D}', keywords: ['food', 'sausage'] },
    { shortcode: 'taco', emoji: '\u{1F32E}', keywords: ['food', 'mexican'] },
    { shortcode: 'burrito', emoji: '\u{1F32F}', keywords: ['food', 'mexican'] },
    { shortcode: 'sandwich', emoji: '\u{1F96A}', keywords: ['food', 'lunch'] },
    { shortcode: 'egg', emoji: '\u{1F95A}', keywords: ['food', 'breakfast'] },
    { shortcode: 'bacon', emoji: '\u{1F953}', keywords: ['food', 'breakfast', 'meat'] },
    { shortcode: 'pancakes', emoji: '\u{1F95E}', keywords: ['food', 'breakfast'] },
    { shortcode: 'bread', emoji: '\u{1F35E}', keywords: ['food', 'loaf'] },
    { shortcode: 'croissant', emoji: '\u{1F950}', keywords: ['food', 'french', 'breakfast'] },
    { shortcode: 'cheese', emoji: '\u{1F9C0}', keywords: ['food', 'dairy'] },
    { shortcode: 'poultry_leg', emoji: '\u{1F357}', keywords: ['food', 'meat', 'chicken'] },
    { shortcode: 'sushi', emoji: '\u{1F363}', keywords: ['food', 'japanese', 'fish'] },
    { shortcode: 'ramen', emoji: '\u{1F35C}', keywords: ['food', 'noodles', 'japanese'] },
    { shortcode: 'spaghetti', emoji: '\u{1F35D}', keywords: ['food', 'pasta', 'italian'] },
    { shortcode: 'curry', emoji: '\u{1F35B}', keywords: ['food', 'indian', 'rice'] },
    { shortcode: 'ice_cream', emoji: '\u{1F368}', keywords: ['food', 'dessert', 'cold'] },
    { shortcode: 'donut', emoji: '\u{1F369}', keywords: ['food', 'dessert', 'sweet'] },
    { shortcode: 'cookie', emoji: '\u{1F36A}', keywords: ['food', 'dessert', 'sweet'] },
    { shortcode: 'cake', emoji: '\u{1F370}', keywords: ['food', 'dessert', 'birthday'] },
    { shortcode: 'birthday', emoji: '\u{1F382}', keywords: ['cake', 'celebrate', 'party'] },
    { shortcode: 'chocolate', emoji: '\u{1F36B}', keywords: ['food', 'dessert', 'sweet'] },
    { shortcode: 'candy', emoji: '\u{1F36C}', keywords: ['food', 'sweet'] },
    { shortcode: 'lollipop', emoji: '\u{1F36D}', keywords: ['food', 'sweet', 'candy'] },
    { shortcode: 'popcorn', emoji: '\u{1F37F}', keywords: ['food', 'movie', 'snack'] },
    { shortcode: 'coffee', emoji: '\u{2615}', keywords: ['drink', 'hot', 'cafe'] },
    { shortcode: 'tea', emoji: '\u{1F375}', keywords: ['drink', 'hot'] },
    { shortcode: 'beer', emoji: '\u{1F37A}', keywords: ['drink', 'alcohol'] },
    { shortcode: 'beers', emoji: '\u{1F37B}', keywords: ['drink', 'alcohol', 'cheers'] },
    { shortcode: 'wine', emoji: '\u{1F377}', keywords: ['drink', 'alcohol', 'red'] },
    { shortcode: 'cocktail', emoji: '\u{1F378}', keywords: ['drink', 'alcohol', 'martini'] },
    { shortcode: 'tropical_drink', emoji: '\u{1F379}', keywords: ['drink', 'alcohol', 'vacation'] },
    { shortcode: 'champagne', emoji: '\u{1F37E}', keywords: ['drink', 'alcohol', 'celebrate'] },
    { shortcode: 'bubble_tea', emoji: '\u{1F9CB}', keywords: ['drink', 'boba'] },

    // Activities & Objects
    { shortcode: 'soccer', emoji: '\u{26BD}', keywords: ['sports', 'football', 'ball'] },
    { shortcode: 'basketball', emoji: '\u{1F3C0}', keywords: ['sports', 'ball'] },
    { shortcode: 'football', emoji: '\u{1F3C8}', keywords: ['sports', 'american'] },
    { shortcode: 'baseball', emoji: '\u{26BE}', keywords: ['sports', 'ball'] },
    { shortcode: 'tennis', emoji: '\u{1F3BE}', keywords: ['sports', 'ball', 'racket'] },
    { shortcode: 'golf', emoji: '\u{26F3}', keywords: ['sports'] },
    { shortcode: 'trophy', emoji: '\u{1F3C6}', keywords: ['winner', 'champion', 'award'] },
    { shortcode: 'medal', emoji: '\u{1F3C5}', keywords: ['winner', 'award', 'first'] },
    { shortcode: 'video_game', emoji: '\u{1F3AE}', keywords: ['gaming', 'controller', 'play'] },
    { shortcode: 'joystick', emoji: '\u{1F579}\u{FE0F}', keywords: ['gaming', 'arcade'] },
    { shortcode: 'dart', emoji: '\u{1F3AF}', keywords: ['bullseye', 'target'] },
    { shortcode: 'bowling', emoji: '\u{1F3B3}', keywords: ['sports', 'ball'] },
    { shortcode: 'guitar', emoji: '\u{1F3B8}', keywords: ['music', 'rock'] },
    { shortcode: 'piano', emoji: '\u{1F3B9}', keywords: ['music', 'keys'] },
    { shortcode: 'microphone', emoji: '\u{1F3A4}', keywords: ['music', 'sing', 'karaoke'] },
    { shortcode: 'headphones', emoji: '\u{1F3A7}', keywords: ['music', 'listen'] },
    { shortcode: 'movie', emoji: '\u{1F3AC}', keywords: ['film', 'cinema', 'clapper'] },
    { shortcode: 'tv', emoji: '\u{1F4FA}', keywords: ['television', 'watch'] },
    { shortcode: 'camera', emoji: '\u{1F4F7}', keywords: ['photo', 'picture'] },
    { shortcode: 'phone', emoji: '\u{1F4F1}', keywords: ['mobile', 'cell', 'smartphone'] },
    { shortcode: 'computer', emoji: '\u{1F4BB}', keywords: ['laptop', 'pc', 'work'] },
    { shortcode: 'keyboard', emoji: '\u{2328}\u{FE0F}', keywords: ['type', 'computer'] },
    { shortcode: 'desktop', emoji: '\u{1F5A5}\u{FE0F}', keywords: ['computer', 'monitor'] },
    { shortcode: 'printer', emoji: '\u{1F5A8}\u{FE0F}', keywords: ['computer', 'paper'] },
    { shortcode: 'mouse_computer', emoji: '\u{1F5B1}\u{FE0F}', keywords: ['computer', 'click'] },
    { shortcode: 'disk', emoji: '\u{1F4BE}', keywords: ['save', 'floppy', 'storage'] },
    { shortcode: 'cd', emoji: '\u{1F4BF}', keywords: ['disc', 'music', 'storage'] },
    { shortcode: 'dvd', emoji: '\u{1F4C0}', keywords: ['disc', 'movie', 'storage'] },
    { shortcode: 'battery', emoji: '\u{1F50B}', keywords: ['power', 'energy'] },
    { shortcode: 'bulb', emoji: '\u{1F4A1}', keywords: ['light', 'idea'] },
    { shortcode: 'flashlight', emoji: '\u{1F526}', keywords: ['light', 'torch'] },
    { shortcode: 'book', emoji: '\u{1F4D6}', keywords: ['read', 'study'] },
    { shortcode: 'books', emoji: '\u{1F4DA}', keywords: ['read', 'study', 'library'] },
    { shortcode: 'notebook', emoji: '\u{1F4D3}', keywords: ['write', 'notes'] },
    { shortcode: 'memo', emoji: '\u{1F4DD}', keywords: ['write', 'notes', 'pencil'] },
    { shortcode: 'pencil', emoji: '\u{270F}\u{FE0F}', keywords: ['write', 'draw'] },
    { shortcode: 'pen', emoji: '\u{1F58A}\u{FE0F}', keywords: ['write'] },
    { shortcode: 'scissors', emoji: '\u{2702}\u{FE0F}', keywords: ['cut'] },
    { shortcode: 'paperclip', emoji: '\u{1F4CE}', keywords: ['attach'] },
    { shortcode: 'pushpin', emoji: '\u{1F4CC}', keywords: ['pin', 'location'] },
    { shortcode: 'folder', emoji: '\u{1F4C1}', keywords: ['file', 'directory'] },
    { shortcode: 'calendar', emoji: '\u{1F4C5}', keywords: ['date', 'schedule'] },
    { shortcode: 'chart', emoji: '\u{1F4CA}', keywords: ['graph', 'stats', 'data'] },
    { shortcode: 'chart_up', emoji: '\u{1F4C8}', keywords: ['graph', 'increase', 'growth'] },
    { shortcode: 'chart_down', emoji: '\u{1F4C9}', keywords: ['graph', 'decrease', 'decline'] },
    { shortcode: 'clipboard', emoji: '\u{1F4CB}', keywords: ['list', 'todo'] },
    { shortcode: 'lock', emoji: '\u{1F512}', keywords: ['secure', 'private'] },
    { shortcode: 'unlock', emoji: '\u{1F513}', keywords: ['open', 'access'] },
    { shortcode: 'key', emoji: '\u{1F511}', keywords: ['lock', 'password', 'access'] },
    { shortcode: 'hammer', emoji: '\u{1F528}', keywords: ['tool', 'build'] },
    { shortcode: 'wrench', emoji: '\u{1F527}', keywords: ['tool', 'fix', 'settings'] },
    { shortcode: 'gear', emoji: '\u{2699}\u{FE0F}', keywords: ['settings', 'options', 'cog'] },
    { shortcode: 'link', emoji: '\u{1F517}', keywords: ['chain', 'url'] },
    { shortcode: 'magnet', emoji: '\u{1F9F2}', keywords: ['attract'] },
    { shortcode: 'hourglass', emoji: '\u{23F3}', keywords: ['time', 'wait', 'sand'] },
    { shortcode: 'alarm', emoji: '\u{23F0}', keywords: ['clock', 'time', 'wake'] },
    { shortcode: 'stopwatch', emoji: '\u{23F1}\u{FE0F}', keywords: ['time', 'speed'] },
    { shortcode: 'timer', emoji: '\u{23F2}\u{FE0F}', keywords: ['clock', 'time'] },
    { shortcode: 'watch', emoji: '\u{231A}', keywords: ['time', 'clock'] },
    { shortcode: 'bell', emoji: '\u{1F514}', keywords: ['notification', 'alert'] },
    { shortcode: 'no_bell', emoji: '\u{1F515}', keywords: ['mute', 'silent'] },
    { shortcode: 'mega', emoji: '\u{1F4E3}', keywords: ['megaphone', 'announce'] },
    { shortcode: 'loudspeaker', emoji: '\u{1F4E2}', keywords: ['announce', 'broadcast'] },
    { shortcode: 'speech', emoji: '\u{1F4AC}', keywords: ['bubble', 'talk', 'chat'] },
    { shortcode: 'thought', emoji: '\u{1F4AD}', keywords: ['bubble', 'think'] },
    { shortcode: 'mail', emoji: '\u{1F4E7}', keywords: ['email', 'message'] },
    { shortcode: 'inbox', emoji: '\u{1F4E5}', keywords: ['email', 'receive'] },
    { shortcode: 'outbox', emoji: '\u{1F4E4}', keywords: ['email', 'send'] },
    { shortcode: 'package', emoji: '\u{1F4E6}', keywords: ['box', 'delivery'] },
    { shortcode: 'gift', emoji: '\u{1F381}', keywords: ['present', 'birthday'] },
    { shortcode: 'balloon', emoji: '\u{1F388}', keywords: ['party', 'celebration'] },
    { shortcode: 'confetti', emoji: '\u{1F38A}', keywords: ['party', 'celebration'] },
    { shortcode: 'ribbon', emoji: '\u{1F380}', keywords: ['decoration', 'pink'] },
    { shortcode: 'money', emoji: '\u{1F4B0}', keywords: ['cash', 'bag', 'dollar'] },
    { shortcode: 'dollar', emoji: '\u{1F4B5}', keywords: ['money', 'cash'] },
    { shortcode: 'credit_card', emoji: '\u{1F4B3}', keywords: ['money', 'payment'] },
    { shortcode: 'gem', emoji: '\u{1F48E}', keywords: ['diamond', 'jewel', 'precious'] },
    { shortcode: 'crown', emoji: '\u{1F451}', keywords: ['king', 'queen', 'royal'] },
    { shortcode: 'ring', emoji: '\u{1F48D}', keywords: ['wedding', 'diamond', 'engaged'] },
    { shortcode: 'lipstick', emoji: '\u{1F484}', keywords: ['makeup', 'cosmetics'] },
    { shortcode: 'pill', emoji: '\u{1F48A}', keywords: ['medicine', 'drug'] },
    { shortcode: 'syringe', emoji: '\u{1F489}', keywords: ['medicine', 'vaccine', 'shot'] },
    { shortcode: 'microscope', emoji: '\u{1F52C}', keywords: ['science', 'lab'] },
    { shortcode: 'telescope', emoji: '\u{1F52D}', keywords: ['science', 'space', 'astronomy'] },
    { shortcode: 'satellite', emoji: '\u{1F6F0}\u{FE0F}', keywords: ['space', 'orbit'] },
    { shortcode: 'rocket', emoji: '\u{1F680}', keywords: ['space', 'launch', 'fast'] },
    { shortcode: 'airplane', emoji: '\u{2708}\u{FE0F}', keywords: ['travel', 'flight'] },
    { shortcode: 'helicopter', emoji: '\u{1F681}', keywords: ['travel', 'flight'] },
    { shortcode: 'car', emoji: '\u{1F697}', keywords: ['vehicle', 'drive', 'auto'] },
    { shortcode: 'taxi', emoji: '\u{1F695}', keywords: ['vehicle', 'cab'] },
    { shortcode: 'bus', emoji: '\u{1F68C}', keywords: ['vehicle', 'transit'] },
    { shortcode: 'train', emoji: '\u{1F686}', keywords: ['vehicle', 'transit', 'rail'] },
    { shortcode: 'bike', emoji: '\u{1F6B2}', keywords: ['bicycle', 'exercise'] },
    { shortcode: 'ship', emoji: '\u{1F6A2}', keywords: ['boat', 'cruise', 'water'] },
    { shortcode: 'anchor', emoji: '\u{2693}', keywords: ['ship', 'boat', 'dock'] },
    { shortcode: 'construction', emoji: '\u{1F6A7}', keywords: ['warning', 'wip', 'work'] },
    { shortcode: 'flag_white', emoji: '\u{1F3F3}\u{FE0F}', keywords: ['surrender', 'peace'] },
    { shortcode: 'flag_black', emoji: '\u{1F3F4}', keywords: ['pirate'] },
    { shortcode: 'checkered_flag', emoji: '\u{1F3C1}', keywords: ['race', 'finish'] },
    { shortcode: 'triangular_flag', emoji: '\u{1F6A9}', keywords: ['flag', 'mark'] }
];

// Plugin key for the emoji suggestion state
var emojiPluginKey = new PluginKey('emoji-suggestion');

/**
 * Find emojis matching the query
 */
function searchEmojis(query) {
    query = (query || '').toLowerCase();
    if (!query) {
        return emojiData.slice(0, 50); // Return first 50 when no query
    }

    return emojiData.filter(function(item) {
        // Match shortcode
        if (item.shortcode.toLowerCase().indexOf(query) !== -1) {
            return true;
        }
        // Match keywords
        if (item.keywords && item.keywords.some(function(kw) {
            return kw.toLowerCase().indexOf(query) !== -1;
        })) {
            return true;
        }
        return false;
    }).slice(0, 20); // Limit results
}

/**
 * Create the Emoji extension
 */
function createEmojiExtension() {
    return Extension.create({
        name: 'emoji',

        addProseMirrorPlugins: function() {
            var editor = this.editor;

            return [
                new Plugin({
                    key: emojiPluginKey,
                    state: {
                        init: function() {
                            return {
                                active: false,
                                query: '',
                                range: null,
                                selectedIndex: 0,
                            };
                        },
                        apply: function(tr, state) {
                            var meta = tr.getMeta(emojiPluginKey);
                            if (meta) {
                                return meta;
                            }

                            // Check if we need to deactivate
                            if (state.active) {
                                var selection = tr.selection;
                                if (!selection.empty) {
                                    return { active: false, query: '', range: null, selectedIndex: 0 };
                                }
                            }

                            return state;
                        },
                    },
                    props: {
                        handleTextInput: function(view, from, to, text) {
                            var state = emojiPluginKey.getState(view.state);

                            // Check for : to start emoji suggestion
                            if (text === ':' && !state.active) {
                                view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
                                    active: true,
                                    query: '',
                                    range: { from: from, to: to + 1 },
                                    selectedIndex: 0,
                                }));
                                showEmojiPopup(editor, view, from + 1, '');
                                return false;
                            }

                            // If active, update the query
                            if (state.active && state.range) {
                                // Check if typing valid characters (letters, numbers, underscore)
                                if (/^[a-zA-Z0-9_]$/.test(text)) {
                                    var newQuery = state.query + text;
                                    view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
                                        active: true,
                                        query: newQuery,
                                        range: { from: state.range.from, to: to + 1 },
                                        selectedIndex: 0,
                                    }));
                                    updateEmojiPopup(editor, newQuery);
                                    return false;
                                }

                                // Check if completing with :
                                if (text === ':') {
                                    var emoji = findExactEmoji(state.query);
                                    if (emoji) {
                                        // Insert emoji and close popup
                                        var tr = view.state.tr.delete(state.range.from - 1, to).insertText(emoji.emoji);
                                        view.dispatch(tr.setMeta(emojiPluginKey, {
                                            active: false,
                                            query: '',
                                            range: null,
                                            selectedIndex: 0,
                                        }));
                                        hideEmojiPopup();
                                        return true;
                                    }
                                }

                                // Space or other characters close the popup
                                view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
                                    active: false,
                                    query: '',
                                    range: null,
                                    selectedIndex: 0,
                                }));
                                hideEmojiPopup();
                            }

                            return false;
                        },
                        handleKeyDown: function(view, event) {
                            var state = emojiPluginKey.getState(view.state);

                            if (!state.active) {
                                return false;
                            }

                            // Handle special keys
                            if (event.key === 'Escape') {
                                view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
                                    active: false,
                                    query: '',
                                    range: null,
                                    selectedIndex: 0,
                                }));
                                hideEmojiPopup();
                                return true;
                            }

                            if (event.key === 'ArrowDown') {
                                event.preventDefault();
                                navigateEmojiPopup(1, view, state);
                                return true;
                            }

                            if (event.key === 'ArrowUp') {
                                event.preventDefault();
                                navigateEmojiPopup(-1, view, state);
                                return true;
                            }

                            if (event.key === 'Enter' || event.key === 'Tab') {
                                event.preventDefault();
                                selectCurrentEmoji(editor, view, state);
                                return true;
                            }

                            if (event.key === 'Backspace') {
                                if (state.query.length > 0) {
                                    var newQuery = state.query.slice(0, -1);
                                    view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
                                        active: true,
                                        query: newQuery,
                                        range: state.range,
                                        selectedIndex: 0,
                                    }));
                                    updateEmojiPopup(editor, newQuery);
                                    return false; // Let default backspace happen
                                } else {
                                    // Close popup when deleting the :
                                    view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
                                        active: false,
                                        query: '',
                                        range: null,
                                        selectedIndex: 0,
                                    }));
                                    hideEmojiPopup();
                                    return false;
                                }
                            }

                            return false;
                        },
                    },
                }),
            ];
        },

        addCommands: function() {
            return {
                insertEmoji: function(emoji) {
                    return function(props) {
                        return props.commands.insertContent(emoji);
                    };
                },
            };
        },
    });
}

// Popup element reference
var emojiPopup = null;
var currentResults = [];
var currentSelectedIndex = 0;

/**
 * Find exact emoji match
 */
function findExactEmoji(shortcode) {
    shortcode = (shortcode || '').toLowerCase();
    for (var i = 0; i < emojiData.length; i++) {
        if (emojiData[i].shortcode.toLowerCase() === shortcode) {
            return emojiData[i];
        }
    }
    return null;
}

/**
 * Show the emoji popup
 */
function showEmojiPopup(editor, view, from, query) {
    hideEmojiPopup();

    emojiPopup = document.createElement('div');
    emojiPopup.className = 'tiptap-emoji-popup';

    currentResults = searchEmojis(query);
    currentSelectedIndex = 0;

    renderEmojiList(editor, view);

    document.body.appendChild(emojiPopup);
    positionEmojiPopup(view, from);
}

/**
 * Update the emoji popup with new results
 */
function updateEmojiPopup(editor, query) {
    if (!emojiPopup) return;

    currentResults = searchEmojis(query);
    currentSelectedIndex = 0;

    renderEmojiList(editor, editor.view);
}

/**
 * Render the emoji list
 */
function renderEmojiList(editor, view) {
    if (!emojiPopup) return;

    emojiPopup.innerHTML = '';

    if (currentResults.length === 0) {
        var emptyDiv = document.createElement('div');
        emptyDiv.className = 'tiptap-emoji-popup__empty';
        emptyDiv.textContent = 'No emojis found';
        emojiPopup.appendChild(emptyDiv);
        return;
    }

    var list = document.createElement('div');
    list.className = 'tiptap-emoji-popup__list';

    currentResults.forEach(function(item, index) {
        var row = document.createElement('div');
        row.className = 'tiptap-emoji-popup__item';
        if (index === currentSelectedIndex) {
            row.classList.add('tiptap-emoji-popup__item--selected');
        }

        var emojiSpan = document.createElement('span');
        emojiSpan.className = 'tiptap-emoji-popup__emoji';
        emojiSpan.textContent = item.emoji;
        row.appendChild(emojiSpan);

        var shortcodeSpan = document.createElement('span');
        shortcodeSpan.className = 'tiptap-emoji-popup__shortcode';
        shortcodeSpan.textContent = ':' + item.shortcode + ':';
        row.appendChild(shortcodeSpan);

        row.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            insertEmoji(editor, view, item);
        });

        row.addEventListener('mouseenter', function() {
            currentSelectedIndex = index;
            renderEmojiList(editor, view);
        });

        list.appendChild(row);
    });

    emojiPopup.appendChild(list);
}

/**
 * Position the emoji popup near the cursor
 */
function positionEmojiPopup(view, from) {
    if (!emojiPopup) return;

    var coords = view.coordsAtPos(from);
    var editorRect = view.dom.getBoundingClientRect();

    var left = coords.left;
    var top = coords.bottom + 5;

    // Keep within viewport
    var popupWidth = 280;
    var popupHeight = 300;

    if (left + popupWidth > window.innerWidth) {
        left = window.innerWidth - popupWidth - 10;
    }
    if (left < 10) {
        left = 10;
    }

    if (top + popupHeight > window.innerHeight) {
        top = coords.top - popupHeight - 5;
    }

    emojiPopup.style.left = left + 'px';
    emojiPopup.style.top = top + 'px';
}

/**
 * Navigate the emoji popup
 */
function navigateEmojiPopup(direction, view, state) {
    if (!emojiPopup || currentResults.length === 0) return;

    currentSelectedIndex += direction;
    if (currentSelectedIndex < 0) {
        currentSelectedIndex = currentResults.length - 1;
    }
    if (currentSelectedIndex >= currentResults.length) {
        currentSelectedIndex = 0;
    }

    view.dispatch(view.state.tr.setMeta(emojiPluginKey, {
        ...state,
        selectedIndex: currentSelectedIndex,
    }));

    renderEmojiList(view.state.plugins[0].spec.editor || { view: view }, view);

    // Scroll to selected
    var selected = emojiPopup.querySelector('.tiptap-emoji-popup__item--selected');
    if (selected) {
        selected.scrollIntoView({ block: 'nearest' });
    }
}

/**
 * Select the current emoji
 */
function selectCurrentEmoji(editor, view, state) {
    if (currentResults.length === 0) return;

    var emoji = currentResults[currentSelectedIndex];
    if (emoji) {
        insertEmoji(editor, view, emoji);
    }
}

/**
 * Insert an emoji
 */
function insertEmoji(editor, view, emojiItem) {
    var state = emojiPluginKey.getState(view.state);

    if (state && state.range) {
        // Delete the :query and insert emoji
        var tr = view.state.tr.delete(state.range.from - 1, view.state.selection.from).insertText(emojiItem.emoji);
        view.dispatch(tr.setMeta(emojiPluginKey, {
            active: false,
            query: '',
            range: null,
            selectedIndex: 0,
        }));
    } else {
        // Just insert emoji
        editor.commands.insertContent(emojiItem.emoji);
    }

    hideEmojiPopup();
}

/**
 * Hide the emoji popup
 */
function hideEmojiPopup() {
    if (emojiPopup) {
        emojiPopup.remove();
        emojiPopup = null;
    }
    currentResults = [];
    currentSelectedIndex = 0;
}

/**
 * Show emoji picker dialog
 */
function showEmojiPickerDialog(editor) {
    var overlay = document.createElement('div');
    overlay.className = 'tiptap-emoji-dialog__overlay';

    var dialog = document.createElement('div');
    dialog.className = 'tiptap-emoji-dialog';

    // Header
    var header = document.createElement('div');
    header.className = 'tiptap-emoji-dialog__header';
    header.innerHTML = '<h3>Insert Emoji</h3><button type="button" class="tiptap-emoji-dialog__close">&times;</button>';
    dialog.appendChild(header);

    // Search
    var searchDiv = document.createElement('div');
    searchDiv.className = 'tiptap-emoji-dialog__search';
    var searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Search emojis...';
    searchInput.className = 'tiptap-emoji-dialog__search-input';
    searchDiv.appendChild(searchInput);
    dialog.appendChild(searchDiv);

    // Grid
    var grid = document.createElement('div');
    grid.className = 'tiptap-emoji-dialog__grid';
    dialog.appendChild(grid);

    // Footer
    var footer = document.createElement('div');
    footer.className = 'tiptap-emoji-dialog__footer';
    footer.innerHTML = '<button type="button" class="tiptap-emoji-dialog__cancel">Cancel</button>';
    dialog.appendChild(footer);

    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    // Render emojis
    function renderGrid(query) {
        grid.innerHTML = '';
        var results = searchEmojis(query);

        results.forEach(function(item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tiptap-emoji-dialog__emoji-btn';
            btn.textContent = item.emoji;
            btn.title = ':' + item.shortcode + ':';
            btn.addEventListener('click', function() {
                editor.commands.insertContent(item.emoji);
                closeDialog();
            });
            grid.appendChild(btn);
        });

        if (results.length === 0) {
            grid.innerHTML = '<div class="tiptap-emoji-dialog__empty">No emojis found</div>';
        }
    }

    // Initial render
    renderGrid('');

    // Search handler
    var searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            renderGrid(searchInput.value);
        }, 150);
    });

    // Close handlers
    function closeDialog() {
        overlay.remove();
        editor.commands.focus();
    }

    header.querySelector('.tiptap-emoji-dialog__close').addEventListener('click', closeDialog);
    footer.querySelector('.tiptap-emoji-dialog__cancel').addEventListener('click', closeDialog);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeDialog();
        }
    });

    // Focus search
    setTimeout(function() {
        searchInput.focus();
    }, 100);
}

export { createEmojiExtension, showEmojiPickerDialog, searchEmojis, emojiData };
