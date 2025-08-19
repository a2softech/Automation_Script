import pandas as pd
from PIL import Image, ImageDraw, ImageFont
import os

# ------------------------- Configuration -------------------------
excel_file = 'names.xlsx'
background_image = '1st.jpg'
font_path = 'NotoSansDevanagari-Bold.ttf'
font_size = 80
output_dir = 'output_invitations'

name_coords = (1970, 1285)
sahparivar_coords = (1970, 1428)
city_coords = (1970, 1570)

name_color = "#800000"
# -----------------------------------------------------------------

# Load Excel
try:
    df = pd.read_excel(excel_file)
except Exception as e:
    print(f"❌ Excel फ़ाइल नहीं पढ़ी जा सकी: {e}")
    exit()

# Load Font
try:
    name_font = ImageFont.truetype(font_path, font_size)
except Exception as e:
    print(f"❌ Font लोड नहीं हुआ: {e}")
    exit()

# Make output folder
os.makedirs(output_dir, exist_ok=True)

# Process each row
for index, row in df.iterrows():
    try:
        name = str(row['name']).strip()
        city = str(row['city']).strip()
        contact = str(row['contact']).strip()

        # Load base image
        image = Image.open(background_image).convert("RGB")
        draw = ImageDraw.Draw(image)

        # Draw text
        draw.text(name_coords, f"{name} Ji", fill=name_color, font=name_font)
        draw.text(sahparivar_coords, "With Family", fill=name_color, font=name_font)
        draw.text(city_coords, city, fill=name_color, font=name_font)

        # Sanitize filename
        safe_name = name.replace(" ", "_")
        safe_city = city.replace(" ", "_")
        filename = f"{safe_name} - {safe_city} - {contact}.jpg"

        image.save(os.path.join(output_dir, filename))
        print(f"✅ {filename} saved.")
    except Exception as e:
        print(f"⚠️ Error processing row {index + 1}: {e}")

print("\n🎉 सभी निमंत्रण पत्र सफलतापूर्वक तैयार हो गए!")
