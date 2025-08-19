from PIL import Image
import os

def make_image_square_if_needed(input_path, output_path):
    image = Image.open(input_path)
    width, height = image.size

    # Convert image to RGB (to handle RGBA or P mode)
    if image.mode in ('RGBA', 'P'):
        image = image.convert('RGB')

    # Already square — just save
    if width == height:
        print(f"Skipping (already square): {input_path}")
        image.save(output_path)
        return

    # Make square with white background
    size = max(width, height)
    new_image = Image.new("RGB", (size, size), (255, 255, 255))
    paste_position = ((size - width) // 2, (size - height) // 2)
    new_image.paste(image, paste_position)

    new_image.save(output_path)
    print(f"Converted to square: {output_path}")

# Paths
base_dir = os.path.dirname(os.path.abspath(__file__))
input_folder = os.path.join(base_dir, 'product-images')
output_folder = os.path.join(base_dir, 'product-images-square')
os.makedirs(output_folder, exist_ok=True)

# Loop over images
for filename in os.listdir(input_folder):
    if filename.lower().endswith(('.png', '.jpg', '.jpeg')):
        input_path = os.path.join(input_folder, filename)
        output_path = os.path.join(output_folder, filename)
        make_image_square_if_needed(input_path, output_path)
